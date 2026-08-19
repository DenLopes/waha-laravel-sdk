<?php

declare(strict_types=1);

namespace DenLopes\Waha\Support;

use InvalidArgumentException;

/**
 * Configuration for human-like, anti-ban conversation behaviour.
 *
 * The policy encodes the WhatsApp-safe sending flow recommended by WAHA:
 *
 *   1. mark the chat as seen,
 *   2. start typing,
 *   3. type word-by-word, with a configurable chance of pausing after a space,
 *   4. stop typing,
 *   5. send the message.
 *
 * The pause is a small "dice roll": after every space the bot has a 12% chance
 * (by default) to stop typing, wait a random interval, then start typing again.
 * On top of that it enforces a random cooldown between consecutive messages and
 * an optional per-window message cap, so a bot does not spam a contact.
 *
 * All values are intentionally expressed in milliseconds/seconds and default to
 * conservative values. Use {@see Pacing::fromConfig()} to build one
 * from waha.conversations, or {@see Pacing::off()} in tests.
 */
final readonly class Pacing
{
    public function __construct(
        public bool $humanize = true,
        public int $minTypingMs = 800,
        public int $maxTypingMs = 3000,
        public float $typingMsPerCharacter = 60.0,
        public int $typingPauseChancePercent = 12,
        public int $minTypingPauseMs = 400,
        public int $maxTypingPauseMs = 1500,
        public int $cooldownMinMs = 30000,
        public int $cooldownMaxMs = 60000,
        public int $maxMessagesPerWindow = 4,
        public int $windowSeconds = 3600,
    ) {
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

        if ($this->cooldownMinMs < 0 || $this->cooldownMaxMs < 0) {
            throw new InvalidArgumentException('Cooldown values must be greater than or equal to zero.');
        }

        if ($this->cooldownMaxMs < $this->cooldownMinMs) {
            throw new InvalidArgumentException('cooldownMaxMs must be greater than or equal to cooldownMinMs.');
        }

        if ($this->maxMessagesPerWindow < 0) {
            throw new InvalidArgumentException('maxMessagesPerWindow must be greater than or equal to zero.');
        }

        if ($this->windowSeconds < 0) {
            throw new InvalidArgumentException('windowSeconds must be greater than or equal to zero.');
        }
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
            minTypingMs: 0,
            maxTypingMs: 0,
            typingMsPerCharacter: 0.0,
            typingPauseChancePercent: 0,
            minTypingPauseMs: 0,
            maxTypingPauseMs: 0,
            cooldownMinMs: 0,
            cooldownMaxMs: 0,
            maxMessagesPerWindow: 0,
            windowSeconds: 0,
        );
    }

    /**
     * Build a policy from the `waha.conversations` config block.
     */
    public static function fromConfig(): self
    {
        $settings = (array) config('waha.conversations', []);

        return new self(
            humanize: (bool) ($settings['humanize'] ?? true),
            minTypingMs: max(0, (int) ($settings['typing_min_ms'] ?? 800)),
            maxTypingMs: max(0, (int) ($settings['typing_max_ms'] ?? 3000)),
            typingMsPerCharacter: max(0.0, (float) ($settings['typing_per_character_ms'] ?? 60.0)),
            typingPauseChancePercent: min(100, max(0, (int) ($settings['typing_pause_chance_percent'] ?? 12))),
            minTypingPauseMs: max(0, (int) ($settings['typing_pause_min_ms'] ?? 400)),
            maxTypingPauseMs: max(0, (int) ($settings['typing_pause_max_ms'] ?? 1500)),
            cooldownMinMs: max(0, (int) ($settings['cooldown_min_ms'] ?? 30000)),
            cooldownMaxMs: max(0, (int) ($settings['cooldown_max_ms'] ?? 60000)),
            maxMessagesPerWindow: max(0, (int) ($settings['max_messages_per_window'] ?? 4)),
            windowSeconds: max(0, (int) ($settings['window_seconds'] ?? 3600)),
        );
    }
}
