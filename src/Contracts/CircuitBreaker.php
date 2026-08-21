<?php

declare(strict_types=1);

namespace DenLopes\Waha\Contracts;

/**
 * Tracks delivery health per session and reports when sends should stop.
 */
interface CircuitBreaker
{
    public function isOpen(string $sessionName): bool;

    public function recordSuccess(string $sessionName): void;

    public function recordFailure(string $sessionName): void;
}
