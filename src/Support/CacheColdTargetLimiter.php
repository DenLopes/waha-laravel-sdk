<?php

declare(strict_types=1);

namespace DenLopes\Waha\Support;

use DenLopes\Waha\Contracts\ColdTargetLimiter;
use DenLopes\Waha\Exceptions\ColdFanoutThrottledException;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

/**
 * Cache-backed unique-target {@see ColdTargetLimiter}.
 *
 * The read-modify-write is serialized behind a cache lock so concurrent workers
 * cannot double-spend the unique-target budget. This is the degraded fallback
 * when Redis is unavailable.
 */
final class CacheColdTargetLimiter implements ColdTargetLimiter
{
    public function __construct(
        private readonly ?string $store = null,
        private readonly string $prefix = 'waha:limiter:',
        private readonly int $lockWaitSeconds = 5,
    ) {}

    public function acquire(string $sessionName, string $chatId, int $maxUniqueTargets, int $windowSeconds): void
    {
        if ($maxUniqueTargets <= 0 || $windowSeconds <= 0) {
            return;
        }

        $key = $this->prefix.$sessionName.':cold:unique_targets';

        $this->locked($sessionName, function () use ($key, $sessionName, $chatId, $maxUniqueTargets, $windowSeconds): void {
            $now = (int) (microtime(true) * 1000);
            $targets = $this->prune((array) $this->cache()->get($key, []), $now, $windowSeconds);

            if (isset($targets[$chatId])) {
                $targets[$chatId] = $now;
                $this->cache()->put($key, $targets, $windowSeconds + 60);

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
            $this->cache()->put($key, $targets, $windowSeconds + 60);
        });
    }

    /**
     * @param  \Closure(): void  $callback
     */
    private function locked(string $sessionName, \Closure $callback): void
    {
        $store = Cache::store($this->store)->getStore();

        if (!$store instanceof LockProvider) {
            $callback();

            return;
        }

        $store->lock($this->prefix.$sessionName.':cold:lock', 10)->block($this->lockWaitSeconds, $callback);
    }

    private function cache(): Repository
    {
        return $this->store !== null ? Cache::store($this->store) : Cache::store();
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
