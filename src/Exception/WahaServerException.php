<?php

declare(strict_types=1);

namespace DenLopes\Waha\Exception;

use Throwable;

/**
 * Thrown when WAHA returns a server-side error (HTTP 5xx).
 */
class WahaServerException extends WahaException
{
    public function __construct(
        string $message = 'WAHA server error.',
        int $code = 500,
        ?Throwable $previous = null,
        array $context = [],
    ) {
        parent::__construct($message, $code, $previous, $context);
    }
}
