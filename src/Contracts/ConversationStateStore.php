<?php

declare(strict_types=1);

namespace DenLopes\Waha\Contracts;

use DenLopes\Waha\Support\PacingState;

/**
 * Persists a conversation's pacing state so it can be shared across instances.
 *
 * The key is the session and chat id joined together. The store owns the
 * storage namespace; the conversation only passes the identity part.
 */
interface ConversationStateStore
{
    public function get(string $key): ?PacingState;

    public function put(string $key, PacingState $state, int $ttlSeconds): void;

    public function forget(string $key): void;

    /**
     * Run the callback while holding a lock scoped to this conversation.
     *
     * @template T
     *
     * @param  int  $ttlSeconds  How long the lock lives before it auto-releases.
     * @param  int  $waitSeconds  How long to wait for the lock before giving up.
     * @param  \Closure(): T  $callback
     * @return T
     */
    public function lock(string $key, int $ttlSeconds, int $waitSeconds, \Closure $callback): mixed;
}
