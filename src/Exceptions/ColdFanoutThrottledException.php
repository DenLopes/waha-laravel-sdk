<?php

declare(strict_types=1);

namespace DenLopes\Waha\Exceptions;

use Throwable;

/**
 * Thrown when a session has exhausted its cold unique-target budget.
 */
final class ColdFanoutThrottledException extends WahaException
{
    public function __construct(
        string $message = 'Cold outreach target limit reached.',
        int $code = 0,
        ?Throwable $previous = null,
        array $context = [],
        public readonly ?string $session = null,
        public readonly int $maxUniqueTargets = 0,
        public readonly int $windowSeconds = 0,
        public readonly int $availableInSeconds = 0,
    ) {
        parent::__construct($message, $code, $previous, $context);
    }
}
