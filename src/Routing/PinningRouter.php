<?php

declare(strict_types=1);

namespace DenLopes\Waha\Routing;

use DenLopes\Waha\Contracts\PinStore;
use DenLopes\Waha\Contracts\SessionRouter;

/**
 * Resolves the host for a session using the pin store, falling back to the
 * configured default host when the session is unknown.
 */
final class PinningRouter implements SessionRouter
{
    public function __construct(
        private readonly PinStore $pins,
        private readonly string $defaultHost = 'primary',
    ) {}

    public function resolveHostKey(?string $hostKey, ?string $sessionName): string
    {
        if ($hostKey !== null && $hostKey !== '') {
            return $hostKey;
        }

        if ($sessionName !== null && $sessionName !== '') {
            $pinned = $this->pins->getHostForSession($sessionName);

            if ($pinned !== null) {
                return $pinned;
            }
        }

        return $this->defaultHost;
    }
}
