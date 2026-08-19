<?php

declare(strict_types=1);

namespace DenLopes\Waha\Exceptions;

use Throwable;

/**
 * Thrown when WAHA rejects the request as invalid (HTTP 400/422).
 */
class RequestException extends ApiException
{
    public function __construct(
        string $message = 'WAHA rejected the request.',
        int $code = 400,
        ?Throwable $previous = null,
        array $context = [],
    ) {
        parent::__construct($message, $code, $previous, $context);
    }
}
