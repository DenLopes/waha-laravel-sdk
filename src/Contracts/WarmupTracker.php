<?php

declare(strict_types=1);

namespace DenLopes\Waha\Contracts;

/**
 * Tracks how long a session has been active to scale cold outreach.
 */
interface WarmupTracker
{
    /**
     * The cold unique-target multiplier for this session.
     *
     * Returns the configured multiplier while the session is young, and 1.0 once
     * it has aged past the warm-up window.
     */
    public function multiplier(string $sessionName): float;

    /**
     * Explicitly (re)stamp the session's first-seen timestamp.
     */
    public function touch(string $sessionName): void;
}
