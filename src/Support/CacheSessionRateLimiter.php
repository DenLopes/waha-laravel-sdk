<?php

declare(strict_types=1);

namespace DenLopes\Waha\Support;

use DenLopes\Waha\Contracts\SessionRateLimiter;
use DenLopes\Waha\Enums\ContactStage;
use DenLopes\Waha\Exceptions\SessionRateLimitedException;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Facades\Cache;

/**
 * Cache-backed fixed-window {@see SessionRateLimiter}.
 *
 * Backs onto Laravel's cache rate limiter so the counter is shared across
 * workers. This is the degraded fallback when Redis is unavailable; it is
 * fixed-window rather than sliding-window.
 */
final class CacheSessionRateLimiter implements SessionRateLimiter
{
    public function __construct(
        private readonly ?string $store = null,
        private readonly string $prefix = 'waha:limiter:',
    ) {}

    public function hit(string $sessionName, ContactStage $stage, TierConfig $tier): void
    {
        $max = $tier->sessionMaxMessages;
        $window = $tier->sessionWindowSeconds;

        if ($max <= 0 || $window <= 0) {
            return;
        }

        $limiter = new RateLimiter(Cache::store($this->store));
        $key = $this->prefix.$sessionName.':'.$stage->value.':messages';

        $attempts = $limiter->hit($key, $window);

        if ($attempts <= $max) {
            return;
        }

        $availableIn = $limiter->availableIn($key);

        throw new SessionRateLimitedException(
            message: sprintf(
                'Session %s reached its limit of %d message(s) per %d second(s).',
                $sessionName,
                $max,
                $window,
            ),
            context: [
                'session'              => $sessionName,
                'stage'                => $stage->value,
                'max_attempts'         => $max,
                'window_seconds'       => $window,
                'available_in_seconds' => $availableIn,
            ],
            session: $sessionName,
            stage: $stage,
            maxAttempts: $max,
            windowSeconds: $window,
            availableInSeconds: $availableIn,
        );
    }
}
