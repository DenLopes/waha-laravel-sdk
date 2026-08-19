<?php

declare(strict_types=1);

namespace DenLopes\Waha\Contracts;

/**
 * Resolves which host should be used for a given WAHA session.
 */
interface SessionRouter
{
    public function resolveHostKey(?string $hostKey, ?string $sessionName): string;
}
