<?php

declare(strict_types=1);

namespace DenLopes\Waha\Exception;

use Throwable;

/**
 * Thrown when the WAHA API key is missing or the request is rejected as
 * unauthorized (HTTP 401/403).
 */
class WahaCredentialsException extends WahaAuthenticationException
{
    public function __construct(
        string $message = 'WAHA API key not configured or invalid. Set WAHA_API_KEY in your environment.',
        int $code = 401,
        ?Throwable $previous = null,
        array $context = [],
    ) {
        parent::__construct($message, $code, $previous, $context);
    }
}
