<?php

declare(strict_types=1);

namespace DenLopes\Waha\Tests\Support;

use DenLopes\Waha\Contracts\WarmupTracker;

/**
 * Configurable {@see WarmupTracker} for pipeline tests.
 */
final class FakeWarmupTracker implements WarmupTracker
{
    public float $multiplierValue = 1.0;

    /**
     * @var list<string>
     */
    public array $touched = [];

    public function multiplier(string $sessionName): float
    {
        return $this->multiplierValue;
    }

    public function touch(string $sessionName): void
    {
        $this->touched[] = $sessionName;
    }
}
