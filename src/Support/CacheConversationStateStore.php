<?php

declare(strict_types=1);

namespace DenLopes\Waha\Support;

use DenLopes\Waha\Contracts\ConversationStateStore;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

/**
 * Stores conversation pacing state in Laravel's cache.
 *
 * This is the default store, which is what makes throttling work across
 * processes and workers instead of only within a single object. The cache
 * store and key prefix are configured under `waha.conversations`.
 */
final class CacheConversationStateStore implements ConversationStateStore
{
    public function __construct(
        private readonly string $prefix = 'waha:conversation:',
        private readonly ?string $store = null,
    ) {}

    public function get(string $key): ?PacingState
    {
        $value = $this->cache()->get($this->prefix.$key);

        return $value instanceof PacingState ? $value : null;
    }

    public function put(string $key, PacingState $state, int $ttlSeconds): void
    {
        $this->cache()->put($this->prefix.$key, $state, $ttlSeconds);
    }

    public function forget(string $key): void
    {
        $this->cache()->forget($this->prefix.$key);
    }

    /**
     * @template T
     *
     * @param  \Closure(): T  $callback
     * @return T
     */
    public function lock(string $key, int $ttlSeconds, int $waitSeconds, \Closure $callback): mixed
    {
        $store = Cache::store($this->store)->getStore();

        if (!$store instanceof LockProvider) {
            return $callback();
        }

        return $store->lock($this->prefix.$key, $ttlSeconds)->block($waitSeconds, $callback);
    }

    private function cache(): Repository
    {
        return $this->store !== null
            ? Cache::store($this->store)
            : Cache::store();
    }
}
