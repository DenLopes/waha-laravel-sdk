<?php

declare(strict_types=1);

namespace DenLopes\Waha\Tests\Support;

use DenLopes\Waha\Contracts\ConversationStateStore;
use DenLopes\Waha\Support\PacingState;

/**
 * In-memory {@see ConversationStateStore} for fluent tests.
 *
 * It keeps state in an array so tests can share a store across conversations
 * without booting Laravel's cache layer.
 */
final class FakeConversationStateStore implements ConversationStateStore
{
    /**
     * @var array<string, PacingState>
     */
    public array $states = [];

    public function get(string $key): ?PacingState
    {
        return $this->states[$key] ?? null;
    }

    public function put(string $key, PacingState $state, int $ttlSeconds): void
    {
        $this->states[$key] = $state;
    }

    public function forget(string $key): void
    {
        unset($this->states[$key]);
    }

    public function lock(string $key, int $ttlSeconds, int $waitSeconds, \Closure $callback): mixed
    {
        return $callback();
    }
}
