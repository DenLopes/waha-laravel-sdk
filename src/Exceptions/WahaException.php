<?php

declare(strict_types=1);

namespace DenLopes\Waha\Exceptions;

use Exception;
use Throwable;

/**
 * Base class for every exception thrown by the WAHA integration.
 *
 * Consumers can catch this type to handle "any WAHA problem" without needing to
 * enumerate the specific subclasses. It also carries a structured context array
 * (HTTP method, endpoint, status, response body, etc.) so callers and loggers
 * can produce actionable diagnostics.
 */
abstract class WahaException extends Exception
{
    /**
     * @param  array<string, mixed>  $context  Structured diagnostic context.
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        protected array $context = [],
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Structured diagnostic context attached to the exception.
     *
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return $this->context;
    }
}
