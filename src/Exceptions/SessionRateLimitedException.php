<?php

declare(strict_types=1);

namespace DenLopes\Waha\Exceptions;

use DenLopes\Waha\Enums\ContactStage;
use Throwable;

/**
 * Thrown when a session reaches its configured message limit.
 *
 * This is the session-wide ceiling, separate from the per-chat cap handled by
 * {@see ConversationThrottledException}. It exposes how long the caller should
 * wait before the current window resets.
 */
class SessionRateLimitedException extends WahaException
{
    public function __construct(
        string $message = 'Session message limit reached.',
        int $code = 0,
        ?Throwable $previous = null,
        array $context = [],
        public readonly ?string $session = null,
        public readonly ?ContactStage $stage = null,
        public readonly int $maxAttempts = 0,
        public readonly int $windowSeconds = 0,
        public readonly int $availableInSeconds = 0,
    ) {
        parent::__construct($message, $code, $previous, $context);
    }
}
