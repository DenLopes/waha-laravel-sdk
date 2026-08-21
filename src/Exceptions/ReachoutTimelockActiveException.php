<?php

declare(strict_types=1);

namespace DenLopes\Waha\Exceptions;

use Throwable;

/**
 * Thrown when WhatsApp has an active reachout timelock on the session.
 */
final class ReachoutTimelockActiveException extends WahaException
{
    public function __construct(
        string $message = 'WhatsApp reachout timelock is active.',
        int $code = 0,
        ?Throwable $previous = null,
        array $context = [],
        public readonly ?string $session = null,
        public readonly int $availableInSeconds = 0,
    ) {
        parent::__construct($message, $code, $previous, $context);
    }
}
