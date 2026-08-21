<?php

declare(strict_types=1);

namespace DenLopes\Waha\Support;

use DenLopes\Waha\Contracts\CircuitBreaker;

/**
 * A disabled circuit breaker that never opens.
 */
final class NullCircuitBreaker implements CircuitBreaker
{
    public function isOpen(string $sessionName): bool
    {
        return false;
    }

    public function recordSuccess(string $sessionName): void
    {
        // nothing to do
    }

    public function recordFailure(string $sessionName): void
    {
        // nothing to do
    }
}
