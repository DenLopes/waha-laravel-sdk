<?php

declare(strict_types=1);

namespace DenLopes\Waha\Exceptions;

use Throwable;

/**
 * Thrown when WhatsApp's reachout message capping has no remaining allowance.
 */
final class ReachoutQuotaExhaustedException extends WahaException
{
    public function __construct(
        string $message = 'WhatsApp reachout quota is exhausted.',
        int $code = 0,
        ?Throwable $previous = null,
        array $context = [],
        public readonly ?string $session = null,
        public readonly ?int $usedQuota = null,
        public readonly ?int $totalQuota = null,
        public readonly int $availableInSeconds = 0,
    ) {
        parent::__construct($message, $code, $previous, $context);
    }
}
