<?php

declare(strict_types=1);

namespace DenLopes\Waha\Support;

use DenLopes\Waha\Contracts\WarmupTracker;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

/**
 * Tracks a session's age to scale cold outreach during warm-up.
 */
final class CacheWarmupTracker implements WarmupTracker
{
    public function __construct(
        private readonly bool $enabled = true,
        private readonly int $ageSeconds = 1209600,
        private readonly float $multiplier = 0.2,
        private readonly ?string $store = null,
        private readonly string $prefix = 'waha:conversation:',
    ) {}

    public function multiplier(string $sessionName): float
    {
        if (!$this->enabled) {
            return 1.0;
        }

        $key = $this->key($sessionName);
        $firstSeen = $this->cache()->get($key);

        if ($firstSeen === null) {
            $firstSeen = time();
            $this->cache()->put($key, $firstSeen, $this->ttlSeconds());
        }

        if (time() - (int) $firstSeen >= $this->ageSeconds) {
            return 1.0;
        }

        return $this->multiplier;
    }

    public function touch(string $sessionName): void
    {
        $this->cache()->put($this->key($sessionName), time(), $this->ttlSeconds());
    }

    private function key(string $sessionName): string
    {
        return $this->prefix.'warmup:'.$sessionName;
    }

    private function ttlSeconds(): int
    {
        return $this->ageSeconds + 60;
    }

    private function cache(): Repository
    {
        return $this->store !== null ? Cache::store($this->store) : Cache::store();
    }
}
