<?php

declare(strict_types=1);

namespace DenLopes\Waha\Support;

use DenLopes\Waha\Contracts\SessionRateLimiter;
use DenLopes\Waha\Enums\ContactStage;
use DenLopes\Waha\Exceptions\SessionRateLimitedException;

/**
 * In-memory sliding-window {@see SessionRateLimiter} for tests.
 */
final class ArraySessionRateLimiter implements SessionRateLimiter
{
    /**
     * @var array<string, list<int>>
     */
    private array $windows = [];

    public function hit(string $sessionName, ContactStage $stage, TierConfig $tier): void
    {
        $max = $tier->sessionMaxMessages;
        $window = $tier->sessionWindowSeconds;

        if ($max <= 0 || $window <= 0) {
            return;
        }

        $key = $this->key($sessionName, $stage);
        $now = (int) (microtime(true) * 1000);

        $entries = array_values(array_filter(
            $this->windows[$key] ?? [],
            static fn (int $timestamp): bool => $timestamp > $now - ($window * 1000),
        ));

        if (count($entries) >= $max) {
            $available = $this->availableInSeconds($entries, $now, $window);

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
                    'available_in_seconds' => $available,
                ],
                session: $sessionName,
                stage: $stage,
                maxAttempts: $max,
                windowSeconds: $window,
                availableInSeconds: $available,
            );
        }

        $entries[] = $now;
        $this->windows[$key] = $entries;
    }

    private function key(string $sessionName, ContactStage $stage): string
    {
        return 'waha:limiter:'.$sessionName.':'.$stage->value.':messages';
    }

    /**
     * @param  list<int>  $entries
     */
    private function availableInSeconds(array $entries, int $now, int $window): int
    {
        $oldest = $entries[0] ?? $now;

        return max(0, (int) ceil((($oldest + ($window * 1000)) - $now) / 1000));
    }
}
