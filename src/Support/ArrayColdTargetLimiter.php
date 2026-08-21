<?php

declare(strict_types=1);

namespace DenLopes\Waha\Support;

use DenLopes\Waha\Contracts\ColdTargetLimiter;
use DenLopes\Waha\Exceptions\ColdFanoutThrottledException;

/**
 * In-memory unique-target {@see ColdTargetLimiter} for tests.
 */
final class ArrayColdTargetLimiter implements ColdTargetLimiter
{
    /**
     * @var array<string, array<string, int>> chat id => last-seen timestamp
     */
    private array $targets = [];

    public function acquire(string $sessionName, string $chatId, int $maxUniqueTargets, int $windowSeconds): void
    {
        if ($maxUniqueTargets <= 0 || $windowSeconds <= 0) {
            return;
        }

        $key = $this->key($sessionName);
        $now = (int) (microtime(true) * 1000);
        $targets = $this->prune($this->targets[$key] ?? [], $now, $windowSeconds);

        if (isset($targets[$chatId])) {
            $targets[$chatId] = $now;
            $this->targets[$key] = $targets;

            return;
        }

        if (count($targets) >= $maxUniqueTargets) {
            $available = $this->availableInSeconds($targets, $now, $windowSeconds);

            throw new ColdFanoutThrottledException(
                message: sprintf(
                    'Session %s reached its limit of %d unique cold target(s) per %d second(s).',
                    $sessionName,
                    $maxUniqueTargets,
                    $windowSeconds,
                ),
                context: [
                    'session'              => $sessionName,
                    'max_unique_targets'   => $maxUniqueTargets,
                    'window_seconds'       => $windowSeconds,
                    'available_in_seconds' => $available,
                ],
                session: $sessionName,
                maxUniqueTargets: $maxUniqueTargets,
                windowSeconds: $windowSeconds,
                availableInSeconds: $available,
            );
        }

        $targets[$chatId] = $now;
        $this->targets[$key] = $targets;
    }

    private function key(string $sessionName): string
    {
        return 'waha:limiter:'.$sessionName.':cold:unique_targets';
    }

    /**
     * @param  array<string, int>  $targets
     * @return array<string, int>
     */
    private function prune(array $targets, int $now, int $windowSeconds): array
    {
        foreach ($targets as $id => $timestamp) {
            if ($timestamp <= $now - ($windowSeconds * 1000)) {
                unset($targets[$id]);
            }
        }

        return $targets;
    }

    /**
     * @param  array<string, int>  $targets
     */
    private function availableInSeconds(array $targets, int $now, int $windowSeconds): int
    {
        $oldest = $targets === [] ? $now : min($targets);

        return max(0, (int) ceil((($oldest + ($windowSeconds * 1000)) - $now) / 1000));
    }
}
