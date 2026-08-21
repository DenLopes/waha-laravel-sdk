<?php

declare(strict_types=1);

namespace DenLopes\Waha\Tests\Support;

use DenLopes\Waha\Contracts\CircuitBreaker;

/**
 * Configurable {@see CircuitBreaker} for pipeline tests.
 */
final class FakeCircuitBreaker implements CircuitBreaker
{
    public bool $open = false;

    /**
     * @var list<string>
     */
    public array $successes = [];

    /**
     * @var list<string>
     */
    public array $failures = [];

    public function isOpen(string $sessionName): bool
    {
        return $this->open;
    }

    public function recordSuccess(string $sessionName): void
    {
        $this->successes[] = $sessionName;
    }

    public function recordFailure(string $sessionName): void
    {
        $this->failures[] = $sessionName;
    }
}
