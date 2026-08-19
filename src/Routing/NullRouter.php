<?php

declare(strict_types=1);

namespace DenLopes\Waha\Routing;

use DenLopes\Waha\Contracts\SessionRouter;

/**
 * No automatic routing: use the explicit host key, or the configured default.
 */
final class NullRouter implements SessionRouter
{
    public function __construct(private readonly string $defaultHost = 'primary') {}

    public function resolveHostKey(?string $hostKey, ?string $sessionName): string
    {
        return $hostKey ?? $this->defaultHost;
    }
}
