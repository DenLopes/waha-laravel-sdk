<?php

declare(strict_types=1);

namespace DenLopes\Waha\Webhooks;

use DenLopes\Waha\Exceptions\WebhookException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Applies additional webhook protections beyond the HMAC signature:
 * timestamp freshness and replay de-duplication.
 */
final class WebhookGuard
{
    public function __construct(
        private readonly int $maxClockSkewMs = 300000,
        private readonly bool $replayEnabled = true,
        private readonly int $replayTtlSeconds = 900,
        private readonly string $replayCachePrefix = 'waha:webhook:',
    ) {}

    /**
     * Reject a timestamp that is too far from the current clock.
     *
     * @throws WebhookException When the timestamp is outside the allowed window.
     */
    public function assertFreshTimestamp(?int $timestampMs): void
    {
        if ($this->maxClockSkewMs <= 0 || $timestampMs === null || $timestampMs <= 0) {
            return;
        }

        $nowMs = (int) Carbon::now()->getTimestampMs();

        if (abs($nowMs - $timestampMs) <= $this->maxClockSkewMs) {
            return;
        }

        throw new WebhookException(
            'The webhook timestamp is outside the allowed window.',
            reason: 'timestamp_outside_window',
            status: 400,
            context: ['timestamp_ms' => $timestampMs],
        );
    }

    /**
     * Whether this request ID has already been seen within the replay window.
     */
    public function isReplay(?string $requestId): bool
    {
        if (!$this->replayEnabled || $requestId === null || $requestId === '' || $this->replayTtlSeconds <= 0) {
            return false;
        }

        // Cache::add returns false when the key already exists.
        return !Cache::add($this->replayCachePrefix.$requestId, 1, $this->replayTtlSeconds);
    }
}
