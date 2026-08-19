<?php

declare(strict_types=1);

namespace DenLopes\Waha\Security;

use DenLopes\Waha\Contracts\ApiKeyProvider;
use DenLopes\Waha\Contracts\HostRegistry;
use DenLopes\Waha\Enums\WahaApiKeyModeEnum;

/**
 * Resolves API keys from host configuration.
 */
final class ConfigApiKeyProvider implements ApiKeyProvider
{
    public function __construct(private readonly HostRegistry $hosts) {}

    public function headerName(string $hostKey): string
    {
        return $this->hosts->get($hostKey)->apiKeyHeader;
    }

    public function adminKey(string $hostKey): ?string
    {
        return $this->hosts->get($hostKey)->apiKey;
    }

    public function sessionKey(string $hostKey, string $sessionName): ?string
    {
        $key = $this->hosts->get($hostKey)->sessionKeys[$sessionName] ?? null;

        return is_string($key) && $key !== '' ? $key : null;
    }

    public function mode(string $hostKey): WahaApiKeyModeEnum
    {
        return $this->hosts->get($hostKey)->mode;
    }
}
