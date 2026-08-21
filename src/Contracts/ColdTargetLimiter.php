<?php

declare(strict_types=1);

namespace DenLopes\Waha\Contracts;

use DenLopes\Waha\Exceptions\ColdFanoutThrottledException;

/**
 * Caps how many unique contacts a session may cold-reach in a window.
 */
interface ColdTargetLimiter
{
    /**
     * Reserve the target for a cold send, throwing once the unique budget is spent.
     *
     * @throws ColdFanoutThrottledException
     */
    public function acquire(string $sessionName, string $chatId, int $maxUniqueTargets, int $windowSeconds): void;
}
