<?php

declare(strict_types=1);

namespace DenLopes\Waha\Support;

use DenLopes\Waha\Enums\ContactStage;
use InvalidArgumentException;

/**
 * Configuration for human-like, anti-ban conversation behaviour.
 *
 * The policy is split into two concerns:
 *
 *   - transport mechanics (thinking, typing, pauses, delay skew), which shape
 *     how a single message is emitted;
 *   - per-stage {@see TierConfig} limits, which shape how many messages may go
 *     out to a contact or session over time.
 *
 * Random delays use a skewed distribution rather than uniform random, so short
 * delays are common and long delays are rare. Use {@see Pacing::fromConfig()} to
 * build one from `waha.conversations`, or {@see Pacing::off()} in tests.
 */
final readonly class Pacing
{
    /**
     * @var array<string, TierConfig>
     */
    private array $tiers;

    public function __construct(
        public bool $humanize = true,
        public int $thinkingMinMs = 600,
        public int $thinkingMaxMs = 2000,
        public float $thinkingMsPerCharacter = 20.0,
        public int $minTypingMs = 800,
        public int $maxTypingMs = 3000,
        public float $typingMsPerCharacter = 60.0,
        public int $typingPauseChancePercent = 4,
        public int $minTypingPauseMs = 400,
        public int $maxTypingPauseMs = 1500,
        public float $delaySkew = 2.0,
        public int $lockWaitSeconds = 300,
        ?array $tiers = null,
    ) {
        if ($this->thinkingMinMs < 0) {
            throw new InvalidArgumentException('thinkingMinMs must be greater than or equal to zero.');
        }

        if ($this->thinkingMaxMs < $this->thinkingMinMs) {
            throw new InvalidArgumentException('thinkingMaxMs must be greater than or equal to thinkingMinMs.');
        }

        if ($this->thinkingMsPerCharacter < 0) {
            throw new InvalidArgumentException('thinkingMsPerCharacter must be greater than or equal to zero.');
        }

        if ($this->minTypingMs < 0) {
            throw new InvalidArgumentException('minTypingMs must be greater than or equal to zero.');
        }

        if ($this->maxTypingMs < $this->minTypingMs) {
            throw new InvalidArgumentException('maxTypingMs must be greater than or equal to minTypingMs.');
        }

        if ($this->typingMsPerCharacter < 0) {
            throw new InvalidArgumentException('typingMsPerCharacter must be greater than or equal to zero.');
        }

        if ($this->typingPauseChancePercent < 0 || $this->typingPauseChancePercent > 100) {
            throw new InvalidArgumentException('typingPauseChancePercent must be between 0 and 100.');
        }

        if ($this->minTypingPauseMs < 0 || $this->maxTypingPauseMs < 0) {
            throw new InvalidArgumentException('Typing pause values must be greater than or equal to zero.');
        }

        if ($this->maxTypingPauseMs < $this->minTypingPauseMs) {
            throw new InvalidArgumentException('maxTypingPauseMs must be greater than or equal to minTypingPauseMs.');
        }

        if ($this->delaySkew <= 0) {
            throw new InvalidArgumentException('delaySkew must be greater than zero.');
        }

        if ($this->lockWaitSeconds <= 0) {
            throw new InvalidArgumentException('lockWaitSeconds must be greater than zero.');
        }

        $this->tiers = $tiers ?? self::defaultTiers();
    }

    /**
     * The conservative defaults used when no configuration is provided.
     */
    public static function default(): self
    {
        return new self;
    }

    /**
     * A policy that performs no humanization and imposes no pacing.
     *
     * Useful for tests and for callers who want to opt out of the anti-ban
     * behaviour entirely.
     */
    public static function off(): self
    {
        return new self(
            humanize: false,
            thinkingMinMs: 0,
            thinkingMaxMs: 0,
            thinkingMsPerCharacter: 0.0,
            minTypingMs: 0,
            maxTypingMs: 0,
            typingMsPerCharacter: 0.0,
            typingPauseChancePercent: 0,
            minTypingPauseMs: 0,
            maxTypingPauseMs: 0,
            tiers: [
                ContactStage::Cold->value  => new TierConfig,
                ContactStage::Warm->value  => new TierConfig,
                ContactStage::Reply->value => new TierConfig,
            ],
        );
    }

    /**
     * Build a policy from the `waha.conversations` config block.
     */
    public static function fromConfig(): self
    {
        $conversations = (array) config('waha.conversations', []);
        $pacing = (array) ($conversations['pacing'] ?? []);
        $tiers = (array) ($conversations['tiers'] ?? []);

        $map = [];

        foreach (ContactStage::cases() as $stage) {
            $map[$stage->value] = TierConfig::fromArray((array) ($tiers[$stage->value] ?? []));
        }

        return new self(
            humanize: (bool) ($pacing['humanize'] ?? true),
            thinkingMinMs: max(0, (int) ($pacing['thinking_min_ms'] ?? 600)),
            thinkingMaxMs: max(0, (int) ($pacing['thinking_max_ms'] ?? 2000)),
            thinkingMsPerCharacter: max(0.0, (float) ($pacing['thinking_per_character_ms'] ?? 20.0)),
            minTypingMs: max(0, (int) ($pacing['typing_min_ms'] ?? 800)),
            maxTypingMs: max(0, (int) ($pacing['typing_max_ms'] ?? 3000)),
            typingMsPerCharacter: max(0.0, (float) ($pacing['typing_per_character_ms'] ?? 60.0)),
            typingPauseChancePercent: min(100, max(0, (int) ($pacing['typing_pause_chance_percent'] ?? 4))),
            minTypingPauseMs: max(0, (int) ($pacing['typing_pause_min_ms'] ?? 400)),
            maxTypingPauseMs: max(0, (int) ($pacing['typing_pause_max_ms'] ?? 1500)),
            delaySkew: max(0.01, (float) ($pacing['delay_skew'] ?? 2.0)),
            lockWaitSeconds: max(1, (int) ($pacing['lock_wait_seconds'] ?? 300)),
            tiers: $map,
        );
    }

    /**
     * The tier configuration for a contact stage.
     */
    public function tier(ContactStage $stage): TierConfig
    {
        return $this->tiers[$stage->value];
    }

    /**
     * @return array<string, TierConfig>
     */
    private static function defaultTiers(): array
    {
        return [
            ContactStage::Cold->value => new TierConfig(
                maxMessagesPerWindow: 1,
                windowSeconds: 86400,
                sessionMaxMessages: 15,
                sessionWindowSeconds: 86400,
                sessionMaxUniqueTargets: 10,
                cooldownMinMs: 60000,
                cooldownMaxMs: 180000,
            ),
            ContactStage::Warm->value => new TierConfig(
                maxMessagesPerWindow: 5,
                windowSeconds: 3600,
                sessionMaxMessages: 100,
                sessionWindowSeconds: 3600,
            ),
            ContactStage::Reply->value => new TierConfig(
                maxMessagesPerWindow: 20,
                windowSeconds: 3600,
                sessionMaxMessages: 300,
                sessionWindowSeconds: 3600,
            ),
        ];
    }
}
