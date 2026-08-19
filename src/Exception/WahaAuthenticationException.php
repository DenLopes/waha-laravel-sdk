<?php

declare(strict_types=1);

namespace DenLopes\Waha\Exception;

use Throwable;

/**
 * Thrown when the WAHA request is rejected as unauthorized (HTTP 401/403).
 */
final class WahaAuthenticationException extends WahaApiException
{
    public function __construct(
        string $message = 'WAHA authentication failed.',
        int $code = 401,
        ?Throwable $previous = null,
        array $context = [],
    ) {
        parent::__construct($message, $code, $previous, $context);
    }
}
