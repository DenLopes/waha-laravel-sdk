<?php

declare(strict_types=1);

namespace DenLopes\Waha\Exception;

use Throwable;

/**
 * Thrown for unexpected or otherwise-unclassified WAHA integration failures.
 */
class WahaIntegrationException extends WahaException
{
    public function __construct(
        string $message = 'Communication with the WAHA service failed.',
        int $code = 500,
        ?Throwable $previous = null,
        array $context = [],
    ) {
        parent::__construct($message, $code, $previous, $context);
    }
}
