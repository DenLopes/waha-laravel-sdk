<?php

declare(strict_types=1);

namespace DenLopes\Waha\Exceptions;

use Throwable;

/**
 * Thrown when a session's delivery-failure circuit breaker is open.
 */
final class CircuitBreakerOpenException extends WahaException
{
    public function __construct(
        string $message = 'Session delivery circuit breaker is open.',
        int $code = 0,
        ?Throwable $previous = null,
        array $context = [],
        public readonly ?string $session = null,
        public readonly int $availableInSeconds = 0,
    ) {
        parent::__construct($message, $code, $previous, $context);
    }
}
