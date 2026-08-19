<?php

declare(strict_types=1);

namespace DenLopes\Waha\Contracts;

/**
 * Stores the session name → host key mapping used for automatic routing.
 */
interface PinStore
{
    public function getHostForSession(string $sessionName): ?string;

    public function pin(string $sessionName, string $hostKey, ?int $ttlSeconds = null): void;

    public function forget(string $sessionName): void;
}
