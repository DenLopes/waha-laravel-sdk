<?php

declare(strict_types=1);

namespace DenLopes\Waha\Tests\Support;

use DenLopes\Waha\Contracts\ReachoutGuard;

/**
 * Recording {@see ReachoutGuard} for pipeline tests.
 */
final class FakeReachoutGuard implements ReachoutGuard
{
    /**
     * @var list<string>
     */
    public array $asserted = [];

    /**
     * @var list<string>
     */
    public array $coldSent = [];

    public function assertAllowed(string $sessionName): void
    {
        $this->asserted[] = $sessionName;
    }

    public function recordColdSent(string $sessionName): void
    {
        $this->coldSent[] = $sessionName;
    }
}
