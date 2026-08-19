<?php

declare(strict_types=1);

namespace DenLopes\Waha\Exception;

use Throwable;

/**
 * Base class for failures attributable to the WAHA API (HTTP status errors,
 * authentication failures, missing sessions, etc.).
 *
 * Consumers can catch this type to handle "any WAHA API error" while still
 * being able to catch the more specific subclasses when they need to.
 */
class WahaApiException extends WahaException
{
    public function __construct(
        string $message = 'The WAHA API returned an error.',
        int $code = 0,
        ?Throwable $previous = null,
        array $context = [],
    ) {
        parent::__construct($message, $code, $previous, $context);
    }
}
