<?php

declare(strict_types=1);

namespace DenLopes\Waha\Exceptions;

use Throwable;

/**
 * Thrown when a conversation reaches its configured per-window message cap.
 *
 * This mirrors WhatsApp's message-capping guidance: pause outreach to this
 * contact and wait for the window to reset rather than re-pairing/restarting the
 * session. The exception exposes how long the caller should wait before trying
 * again so it can be queued or surfaced to an operator.
 */
class ConversationThrottledException extends WahaException
{
    public function __construct(
        string $message = 'Conversation message limit reached.',
        int $code = 0,
        ?Throwable $previous = null,
        array $context = [],
        public readonly ?string $chatId = null,
        public readonly int $availableInSeconds = 0,
    ) {
        parent::__construct($message, $code, $previous, $context);
    }
}
