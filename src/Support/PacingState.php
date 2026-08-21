<?php

declare(strict_types=1);

namespace DenLopes\Waha\Support;

use DenLopes\Waha\Contracts\ConversationStateStore;

/**
 * The pacing state shared across one conversation.
 *
 * It keeps the timestamp of the last sent message and the timestamps of the
 * messages sent inside the current window. The conversation reads and writes
 * this object through a {@see ConversationStateStore},
 * which is what makes pacing global instead of per-instance.
 */
final readonly class PacingState
{
    /**
     * @param  list<float>  $sentAt
     */
    public function __construct(
        public ?float $lastSentAt = null,
        public array $sentAt = [],
    ) {}

    public static function empty(): self
    {
        return new self;
    }

    /**
     * Record a sent message at the given unix timestamp.
     *
     * When a window is active, the timestamp is appended and everything older
     * than the window is dropped. `lastSentAt` is always updated.
     */
    public function record(float $now, int $windowSeconds): self
    {
        $sentAt = $this->sentAt;

        if ($windowSeconds > 0) {
            $sentAt[] = $now;
            $sentAt = self::pruneTimestamps($sentAt, $now, $windowSeconds);
        }

        return new self($now, $sentAt);
    }

    /**
     * Drop timestamps that fall outside the rolling window.
     */
    public function prune(float $now, int $windowSeconds): self
    {
        if ($windowSeconds <= 0) {
            return new self($this->lastSentAt, []);
        }

        return new self($this->lastSentAt, self::pruneTimestamps($this->sentAt, $now, $windowSeconds));
    }

    /**
     * @param  list<float>  $timestamps
     * @return list<float>
     */
    private static function pruneTimestamps(array $timestamps, float $now, int $windowSeconds): array
    {
        $cutoff = $now - $windowSeconds;

        return array_values(array_filter(
            $timestamps,
            static fn (float $timestamp): bool => $timestamp > $cutoff,
        ));
    }
}
