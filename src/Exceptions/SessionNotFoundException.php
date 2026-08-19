<?php

declare(strict_types=1);

namespace DenLopes\Waha\Exceptions;

use Throwable;

/**
 * Thrown when a WAHA session-scoped request targets a session that does not
 * exist (HTTP 404 on a session endpoint).
 */
final class SessionNotFoundException extends ApiException
{
    public function __construct(
        string $message = 'The requested WAHA session was not found.',
        int $code = 404,
        ?Throwable $previous = null,
        array $context = [],
    ) {
        parent::__construct($message, $code, $previous, $context);
    }
}
