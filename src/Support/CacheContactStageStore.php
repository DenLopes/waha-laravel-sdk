<?php

declare(strict_types=1);

namespace DenLopes\Waha\Support;

use DenLopes\Waha\Contracts\ContactStageStore;
use DenLopes\Waha\Enums\ContactStage;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

/**
 * Stores contact relationship stages in Laravel's cache.
 */
final class CacheContactStageStore implements ContactStageStore
{
    public function __construct(
        private readonly string $prefix = 'waha:conversation:',
        private readonly ?string $store = null,
        private readonly int $ttlSeconds = 2592000,
    ) {}

    public function get(string $session, string $chatId): ?ContactStage
    {
        $value = $this->cache()->get($this->key($session, $chatId));

        return $value instanceof ContactStage ? $value : null;
    }

    public function mark(string $session, string $chatId, ContactStage $stage, ?int $ttlSeconds = null): void
    {
        $this->cache()->put($this->key($session, $chatId), $stage, $ttlSeconds ?? $this->ttlSeconds);
    }

    public function forget(string $session, string $chatId): void
    {
        $this->cache()->forget($this->key($session, $chatId));
    }

    private function key(string $session, string $chatId): string
    {
        return $this->prefix.'stage:'.$session.':'.$chatId;
    }

    private function cache(): Repository
    {
        return $this->store !== null ? Cache::store($this->store) : Cache::store();
    }
}
