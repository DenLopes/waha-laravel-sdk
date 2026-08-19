<?php

declare(strict_types=1);

namespace DenLopes\Waha\Exception;

use Throwable;

/**
 * Thrown when an inbound WAHA webhook fails verification or dispatch setup.
 *
 * Carries a machine-readable {@see self::$reason} and the HTTP status the webhook
 * controller should return, so the HTTP layer can translate failures without
 * re-implementing the verification policy.
 */
final class WahaWebhookException extends WahaException
{
    public function __construct(
        string $message = '',
        public readonly string $reason = 'webhook_error',
        public readonly int $status = 400,
        ?Throwable $previous = null,
        array $context = [],
    ) {
        parent::__construct($message, $status, $previous, ['reason' => $reason] + $context);
    }
}
