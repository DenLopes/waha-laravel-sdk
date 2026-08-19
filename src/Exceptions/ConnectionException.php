<?php

declare(strict_types=1);

namespace DenLopes\Waha\Exceptions;

use Throwable;

/**
 * Thrown when WAHA cannot be reached (connection failure or timeout).
 */
class ConnectionException extends WahaException
{
    public function __construct(
        string $message = 'Unable to connect to the WAHA server.',
        int $code = 0,
        ?Throwable $previous = null,
        array $context = [],
    ) {
        parent::__construct($message, $code, $previous, $context);
    }
}
