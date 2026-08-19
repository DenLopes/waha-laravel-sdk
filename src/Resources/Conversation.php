<?php

declare(strict_types=1);

namespace DenLopes\Waha\Resources;

use DenLopes\Waha\Contracts\Conversation as ConversationContract;
use DenLopes\Waha\Exceptions\ConversationThrottledException;
use DenLopes\Waha\Session;
use DenLopes\Waha\Support\Pacing;

/**
 * A fluent wrapper around a chat that sends messages in a human-like way.
 *
 * {@see Conversation} follows the anti-ban flow recommended by WAHA:
 *
 *     markRead -> startTyping -> (random typing delay) -> stopTyping -> sendText
 *
 * It also spaces out consecutive messages with a random cooldown and enforces an
 * optional per-window message cap. When the cap is reached it throws a
 * {@see ConversationThrottledException} instead of silently hammering the
 * contact, so callers can pause/queue their outreach.
 *
 * Pacing state is intentionally kept on the instance: create one conversation
 * per logical contact conversation and reuse it for the lifetime of that flow.
 * For cross-process/global throttling combine this with Laravel's queue or rate
 * limiter primitives.
 */
final class Conversation implements ConversationContract
{
    private ?float $lastSentAt = null;

    /**
     * Timestamps (unix seconds, fractional) of messages sent in the current window.
     *
     * @var list<float>
     */
    private array $sentAt = [];

    /**
     * @param  \Closure(int):void|null  $sleep  Sleep in milliseconds; inject a no-op in tests.
     */
    public function __construct(
        private readonly Chat $chat,
        private readonly Pacing $policy = new Pacing,
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
     * Send a text message using the WhatsApp-safe, human-like flow.
     */
    public function send(
        string $text,
        ?string $replyTo = null,
        ?bool $linkPreview = true,
        ?string $id = null,
        ?bool $linkPreviewHighQuality = false,
    ): Message {
        $this->enforceWindowLimit();
        $this->enforceCooldown();

        if ($this->policy->humanize) {
            $this->chat->markRead();
            $this->typeHuman($text);
        }

        $message = $this->chat->sendMessage($text, $replyTo, $linkPreview, $id, $linkPreviewHighQuality);

        $this->recordSent();

        return $message;
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

    /**
     * Mark messages in the chat as read.
     *
     * @param  string[]|null  $messageIds
     */
    public function markRead(?array $messageIds = null, ?string $participant = null): static
    {
        $this->chat->markRead($messageIds, $participant);

        return $this;
    }

    /**
     * Start the typing indicator.
     */
    public function startTyping(): static
    {
        $this->chat->startTyping();

        return $this;
    }

    /**
     * Stop the typing indicator.
     */
    public function stopTyping(): static
    {
        $this->chat->stopTyping();

        return $this;
    }

    /**
     * Pause for the given number of milliseconds.
     */
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
        $this->lastSentAt = null;
        $this->sentAt = [];

        return $this;
    }

    private function enforceWindowLimit(): void
    {
        $max = $this->policy->maxMessagesPerWindow;
        $window = $this->policy->windowSeconds;

        if ($max <= 0 || $window <= 0) {
            return;
        }

        $now = microtime(true);
        $this->pruneWindow($now);

        if (count($this->sentAt) < $max) {
            return;
        }

        $availableAt = $this->sentAt[0] + $window;
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
                'max_messages_per_window' => $max,
                'window_seconds'          => $window,
                'available_in_seconds'    => $availableInSeconds,
            ],
            chatId: $this->chatId(),
            availableInSeconds: $availableInSeconds,
        );
    }

    private function enforceCooldown(): void
    {
        if ($this->lastSentAt === null) {
            return;
        }

        $targetMs = $this->randomDelayMs($this->policy->cooldownMinMs, $this->policy->cooldownMaxMs);

        if ($targetMs <= 0) {
            return;
        }

        $elapsedMs = (int) floor((microtime(true) - $this->lastSentAt) * 1000);

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

        return $min === $max ? $min : random_int($min, $max);
    }

    private function recordSent(): void
    {
        $now = microtime(true);
        $this->lastSentAt = $now;

        if ($this->policy->maxMessagesPerWindow > 0 && $this->policy->windowSeconds > 0) {
            $this->sentAt[] = $now;
            $this->pruneWindow($now);
        }
    }

    private function pruneWindow(float $now): void
    {
        $window = $this->policy->windowSeconds;

        if ($window <= 0) {
            $this->sentAt = [];

            return;
        }

        $cutoff = $now - $window;
        $this->sentAt = array_values(array_filter(
            $this->sentAt,
            static fn (float $timestamp): bool => $timestamp > $cutoff,
        ));
    }
}
