<?php

declare(strict_types=1);

namespace DenLopes\Waha\Contracts;

use DenLopes\Waha\Exceptions\ReachoutQuotaExhaustedException;
use DenLopes\Waha\Exceptions\ReachoutTimelockActiveException;

/**
 * Gate cold sends on WhatsApp's own reachout signals (capping and timelock).
 */
interface ReachoutGuard
{
    /**
     * @throws ReachoutQuotaExhaustedException|ReachoutTimelockActiveException
     */
    public function assertAllowed(string $sessionName): void;

    public function recordColdSent(string $sessionName): void;
}
