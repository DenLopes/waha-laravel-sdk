<?php

declare(strict_types=1);

namespace DenLopes\Waha\Exception;

use Throwable;

/**
 * Thrown when the requested WAHA resource does not exist (HTTP 404).
 */
class NoDataException extends WahaException
{
    public function __construct(
        string $message = 'No data found for the requested resource.',
        int $code = 404,
        ?Throwable $previous = null,
        array $context = [],
    ) {
        parent::__construct($message, $code, $previous, $context);
    }
}
