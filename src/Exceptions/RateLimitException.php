<?php

declare(strict_types=1);

namespace DenLopes\Waha\Exceptions;

use Throwable;

/**
 * Thrown when WAHA returns an HTTP 429 rate-limit response.
 */
class RateLimitException extends ApiException
{
    public function __construct(
        string $message = 'WAHA rate limit exceeded.',
        int $code = 429,
        ?Throwable $previous = null,
        array $context = [],
    ) {
        parent::__construct($message, $code, $previous, $context);
    }
}
