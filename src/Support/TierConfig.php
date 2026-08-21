<?php

declare(strict_types=1);

namespace DenLopes\Waha\Support;

use InvalidArgumentException;

/**
 * The quota and pacing limits for a single contact stage.
 *
 * Every field defaults to "unlimited": zero messages means no per-chat cap and
 * no session cap, a null {@see self::$sessionMaxUniqueTargets} means no unique
 * cold-target budget, and zero cooldowns mean no enforced inter-message wait.
 */
final readonly class TierConfig
{
    public function __construct(
        public int $maxMessagesPerWindow = 0,
        public int $windowSeconds = 0,
        public int $sessionMaxMessages = 0,
        public int $sessionWindowSeconds = 0,
        public ?int $sessionMaxUniqueTargets = null,
        public int $cooldownMinMs = 0,
        public int $cooldownMaxMs = 0,
    ) {
        if ($this->cooldownMaxMs < $this->cooldownMinMs) {
            throw new InvalidArgumentException('cooldownMaxMs must be greater than or equal to cooldownMinMs.');
        }
    }

    /**
     * Build a tier from the `waha.conversations.tiers.*` config block.
     *
     * @param  array<string, mixed>  $config
     */
    public static function fromArray(array $config): self
    {
        return new self(
            maxMessagesPerWindow: max(0, (int) ($config['max_messages_per_window'] ?? 0)),
            windowSeconds: max(0, (int) ($config['window_seconds'] ?? 0)),
            sessionMaxMessages: max(0, (int) ($config['session_max_messages'] ?? 0)),
            sessionWindowSeconds: max(0, (int) ($config['session_window_seconds'] ?? 0)),
            sessionMaxUniqueTargets: array_key_exists('session_max_unique_targets', $config)
                ? max(0, (int) $config['session_max_unique_targets'])
                : null,
            cooldownMinMs: max(0, (int) ($config['cooldown_min_ms'] ?? 0)),
            cooldownMaxMs: max(0, (int) ($config['cooldown_max_ms'] ?? 0)),
        );
    }
}
