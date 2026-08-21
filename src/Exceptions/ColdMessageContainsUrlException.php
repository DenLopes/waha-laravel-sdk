<?php

declare(strict_types=1);

namespace DenLopes\Waha\Exceptions;

use Throwable;

/**
 * Thrown when a cold message contains a URL and the policy rejects URLs.
 */
final class ColdMessageContainsUrlException extends WahaException
{
    public function __construct(
        string $message = 'Cold outreach messages may not contain URLs.',
        int $code = 0,
        ?Throwable $previous = null,
        array $context = [],
        public readonly ?string $session = null,
        public readonly ?string $chatId = null,
        public readonly string $text = '',
    ) {
        parent::__construct($message, $code, $previous, $context);
    }
}
