<?php

declare(strict_types=1);

namespace DenLopes\Waha\Resources;

use DenLopes\Waha\Contracts\CircuitBreaker;
use DenLopes\Waha\Contracts\ColdTargetLimiter;
use DenLopes\Waha\Contracts\ContactStageStore;
use DenLopes\Waha\Contracts\Conversation as ConversationContract;
use DenLopes\Waha\Contracts\ConversationStateStore;
use DenLopes\Waha\Contracts\ReachoutGuard;
use DenLopes\Waha\Contracts\SessionRateLimiter;
use DenLopes\Waha\Contracts\WarmupTracker;
use DenLopes\Waha\Enums\ContactStage;
use DenLopes\Waha\Exceptions\CircuitBreakerOpenException;
use DenLopes\Waha\Exceptions\ColdMessageContainsUrlException;
use DenLopes\Waha\Exceptions\ConversationThrottledException;
use DenLopes\Waha\Session;
use DenLopes\Waha\Support\Pacing;
use DenLopes\Waha\Support\PacingState;
use DenLopes\Waha\Support\TierConfig;

/**
 * A fluent wrapper around a chat that sends messages in a human-like way.
 *
 * Each send is gated, in order, by the delivery circuit breaker, the contact's
 * relationship stage, WhatsApp's own reachout signals, the per-chat window cap,
 * the per-session stage quota, and the cold unique-target budget, before the
 * humanized dispatch happens.
 */
final class Conversation implements ConversationContract
{
    private ?ContactStage $stageOverride = null;

    /**
     * @param  \Closure(int):void|null  $sleep  Sleep in milliseconds; inject a no-op in tests.
     */
    public function __construct(
        private readonly Chat $chat,
        private readonly Pacing $policy,
        private readonly ConversationStateStore $stateStore,
        private readonly ContactStageStore $contactStageStore,
        private readonly SessionRateLimiter $sessionLimiter,
        private readonly ColdTargetLimiter $coldTargetLimiter,
        private readonly ReachoutGuard $reachoutGuard,
        private readonly WarmupTracker $warmupTracker,
        private readonly CircuitBreaker $circuitBreaker,
        private readonly bool $throwOnColdUrls = false,
        private readonly int $circuitBreakerCooldownSeconds = 300,
        private readonly ?\Closure $sleep = null,
    ) {}

    public function chat(): Chat
    {
        return $this->chat;
    }

    public function session(): Session
    {
        return $this->chat->session();
    }

    public function chatId(): string
    {
        return $this->chat->chatId();
    }

    public function policy(): Pacing
    {
        return $this->policy;
    }

    /**
     * Force a contact stage for subsequent sends, bypassing automatic resolution.
     */
    public function withStage(ContactStage $stage): static
    {
        $this->stageOverride = $stage;

        return $this;
    }

    /**
     * Send a text message using the WhatsApp-safe, human-like flow.
     */
    public function send(
        string $text,
        ?string $replyTo = null,
        ?bool $linkPreview = true,
        ?string $id = null,
        ?bool $linkPreviewHighQuality = false,
    ): Message {
        $sessionName = $this->session()->value();

        if ($this->circuitBreaker->isOpen($sessionName)) {
            throw new CircuitBreakerOpenException(
                message: sprintf('Session %s delivery circuit breaker is open.', $sessionName),
                context: [
                    'session'              => $sessionName,
                    'available_in_seconds' => $this->circuitBreakerCooldownSeconds,
                ],
                session: $sessionName,
                availableInSeconds: $this->circuitBreakerCooldownSeconds,
            );
        }

        $stage = $this->resolveStage($replyTo);

        if ($stage !== ContactStage::Cold) {
            $this->contactStageStore->mark($sessionName, $this->chatId(), ContactStage::Warm);
        }

        if ($stage === ContactStage::Cold) {
            if ($this->throwOnColdUrls && $this->containsUrl($text)) {
                throw new ColdMessageContainsUrlException(
                    message: 'Cold outreach messages may not contain URLs.',
                    context: [
                        'session' => $sessionName,
                        'chat_id' => $this->chatId(),
                        'text'    => $text,
                    ],
                    session: $sessionName,
                    chatId: $this->chatId(),
                    text: $text,
                );
            }

            $linkPreview = false;
            $linkPreviewHighQuality = false;

            $this->reachoutGuard->assertAllowed($sessionName);
        }

        return $this->stateStore->lock(
            $this->stateKey(),
            $this->lockTtlSecondsFor($text),
            $this->policy->lockWaitSeconds,
            function () use ($sessionName, $stage, $text, $replyTo, $linkPreview, $id, $linkPreviewHighQuality): Message {
                $state = $this->loadState();
                $now = microtime(true);
                $tier = $this->policy->tier($stage);

                $state = $state->prune($now, $tier->windowSeconds);
                $this->enforceWindowLimit($state, $now, $tier, $stage);
                $this->sessionLimiter->hit($sessionName, $stage, $tier);

                if ($stage === ContactStage::Cold) {
                    $this->enforceColdTargetBudget($sessionName, $tier);
                }

                $this->enforceCooldown($state, $tier);

                if ($this->policy->humanize) {
                    $this->chat->markRead();
                    $this->wait($this->thinkingDelayMs($text));
                    $this->typeHuman($text);
                }

                $message = $this->chat->sendMessage($text, $replyTo, $linkPreview, $id, $linkPreviewHighQuality);

                $this->saveState($state->record(microtime(true), $tier->windowSeconds));

                if ($stage === ContactStage::Cold) {
                    $this->reachoutGuard->recordColdSent($sessionName);
                }

                return $message;
            }
        );
    }

    /**
     * Reply to a specific message using the same human-like flow.
     */
    public function reply(
        string $text,
        string $messageId,
        ?bool $linkPreview = true,
        ?string $id = null,
        ?bool $linkPreviewHighQuality = false,
    ): Message {
        return $this->send($text, $messageId, $linkPreview, $id, $linkPreviewHighQuality);
    }

    public function markRead(?array $messageIds = null, ?string $participant = null): static
    {
        $this->chat->markRead($messageIds, $participant);

        return $this;
    }

    public function startTyping(): static
    {
        $this->chat->startTyping();

        return $this;
    }

    public function stopTyping(): static
    {
        $this->chat->stopTyping();

        return $this;
    }

    public function wait(int $milliseconds): static
    {
        if ($milliseconds > 0) {
            $sleep = $this->sleep ?? static fn (int $ms) => usleep($ms * 1000);
            $sleep($milliseconds);
        }

        return $this;
    }

    /**
     * Forget the pacing state so the next message is treated as a fresh start.
     */
    public function reset(): static
    {
        $this->stateStore->forget($this->stateKey());

        return $this;
    }

    private function resolveStage(?string $replyTo): ContactStage
    {
        if ($this->stageOverride !== null) {
            return $this->stageOverride;
        }

        if ($replyTo !== null) {
            return ContactStage::Reply;
        }

        return $this->contactStageStore->get($this->session()->value(), $this->chatId()) ?? ContactStage::Cold;
    }

    private function enforceColdTargetBudget(string $sessionName, TierConfig $tier): void
    {
        $base = $tier->sessionMaxUniqueTargets;

        if ($base === null || $base <= 0) {
            return;
        }

        $multiplier = $this->warmupTracker->multiplier($sessionName);
        $effectiveMax = max(1, (int) round($base * $multiplier));

        $this->coldTargetLimiter->acquire($sessionName, $this->chatId(), $effectiveMax, $tier->sessionWindowSeconds);
    }

    private function enforceWindowLimit(PacingState $state, float $now, TierConfig $tier, ContactStage $stage): void
    {
        $max = $tier->maxMessagesPerWindow;
        $window = $tier->windowSeconds;

        if ($max <= 0 || $window <= 0) {
            return;
        }

        if (count($state->sentAt) < $max) {
            return;
        }

        $availableAt = $state->sentAt[0] + $window;
        $availableInSeconds = max(0, (int) ceil($availableAt - $now));

        throw new ConversationThrottledException(
            message: sprintf(
                'Conversation with %s reached its limit of %d message(s) per %d second(s).',
                $this->chatId(),
                $max,
                $window,
            ),
            context: [
                'chat_id'                 => $this->chatId(),
                'session'                 => $this->session()->value(),
                'stage'                   => $stage->value,
                'max_messages_per_window' => $max,
                'window_seconds'          => $window,
                'available_in_seconds'    => $availableInSeconds,
            ],
            chatId: $this->chatId(),
            availableInSeconds: $availableInSeconds,
        );
    }

    private function enforceCooldown(PacingState $state, TierConfig $tier): void
    {
        if ($state->lastSentAt === null) {
            return;
        }

        $targetMs = $this->randomDelayMs($tier->cooldownMinMs, $tier->cooldownMaxMs);

        if ($targetMs <= 0) {
            return;
        }

        $elapsedMs = (int) floor((microtime(true) - $state->lastSentAt) * 1000);

        if ($elapsedMs < $targetMs) {
            $this->wait($targetMs - $elapsedMs);
        }
    }

    private function typeHuman(string $text): void
    {
        $this->chat->startTyping();

        $this->wait($this->startTypingDelayMs());

        $chunks = preg_split('/ /', $text);
        $chunks = $chunks === false ? [$text] : $chunks;
        $last = count($chunks) - 1;

        foreach ($chunks as $index => $chunk) {
            $this->wait($this->chunkTypingDelayMs($chunk));

            if ($index < $last) {
                $this->maybePauseTyping();
            }
        }

        $this->chat->stopTyping();
    }

    private function thinkingDelayMs(string $text): int
    {
        $base = $this->randomDelayMs($this->policy->thinkingMinMs, $this->policy->thinkingMaxMs);

        return $base + (int) round(mb_strlen($text) * $this->policy->thinkingMsPerCharacter);
    }

    private function lockTtlSecondsFor(string $text): int
    {
        $length = mb_strlen($text);

        $thinkingMs = $this->policy->thinkingMaxMs + (int) ceil($length * $this->policy->thinkingMsPerCharacter);
        $typingMs = $this->policy->maxTypingMs + (int) ceil($length * $this->policy->typingMsPerCharacter);
        $pauseMs = substr_count($text, ' ') * $this->policy->maxTypingPauseMs;

        // Buffer for the WAHA round trip and any retries.
        $totalMs = $thinkingMs + $typingMs + $pauseMs + 10000;

        return (int) ceil($totalMs / 1000);
    }

    private function startTypingDelayMs(): int
    {
        return $this->randomDelayMs($this->policy->minTypingMs, $this->policy->maxTypingMs);
    }

    private function chunkTypingDelayMs(string $chunk): int
    {
        return (int) round(mb_strlen($chunk) * $this->policy->typingMsPerCharacter);
    }

    private function maybePauseTyping(): void
    {
        $chance = $this->policy->typingPauseChancePercent;

        if ($chance <= 0 || random_int(1, 100) > $chance) {
            return;
        }

        $this->chat->stopTyping();
        $this->wait($this->pauseDelayMs());
        $this->chat->startTyping();
    }

    private function pauseDelayMs(): int
    {
        return $this->randomDelayMs($this->policy->minTypingPauseMs, $this->policy->maxTypingPauseMs);
    }

    private function randomDelayMs(int $min, int $max): int
    {
        $min = max(0, $min);
        $max = max($min, $max);

        if ($min === $max) {
            return $min;
        }

        $unit = random_int(0, 1000000) / 1000000.0;

        return (int) round($min + ($max - $min) * ($unit ** $this->policy->delaySkew));
    }

    private function containsUrl(string $text): bool
    {
        return (bool) preg_match('/https?:\/\//i', $text);
    }

    private function loadState(): PacingState
    {
        return $this->stateStore->get($this->stateKey()) ?? PacingState::empty();
    }

    private function saveState(PacingState $state): void
    {
        $this->stateStore->put($this->stateKey(), $state, $this->stateTtlSeconds());
    }

    private function stateKey(): string
    {
        return $this->session()->value().':'.$this->chatId();
    }

    private function stateTtlSeconds(): int
    {
        $span = 0;

        foreach (ContactStage::cases() as $stage) {
            $tier = $this->policy->tier($stage);
            $span = max($span, $tier->windowSeconds, (int) ceil($tier->cooldownMaxMs / 1000));
        }

        return max(60, $span + 60);
    }
}
