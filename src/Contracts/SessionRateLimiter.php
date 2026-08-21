<?php

declare(strict_types=1);

namespace DenLopes\Waha\Contracts;

use DenLopes\Waha\Enums\ContactStage;
use DenLopes\Waha\Exceptions\SessionRateLimitedException;
use DenLopes\Waha\Support\TierConfig;

/**
 * Enforces a session-wide message limit across every chat on a session,
 * split by contact stage.
 */
interface SessionRateLimiter
{
    /**
     * Record a message for the session and throw when the stage's limit is reached.
     *
     * @throws SessionRateLimitedException
     */
    public function hit(string $sessionName, ContactStage $stage, TierConfig $tier): void;
}
