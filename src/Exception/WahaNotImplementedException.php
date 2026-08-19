<?php

declare(strict_types=1);

namespace DenLopes\Waha\Exception;

use Throwable;

/**
 * Thrown when WAHA returns HTTP 501 — the requested endpoint is not implemented
 * by the currently configured engine (e.g. GOWS).
 */
class WahaNotImplementedException extends WahaApiException
{
    public function __construct(
        string $message = 'The requested WAHA endpoint is not implemented by the current engine.',
        int $code = 501,
        ?Throwable $previous = null,
        array $context = [],
    ) {
        parent::__construct($message, $code, $previous, $context);
    }
}
