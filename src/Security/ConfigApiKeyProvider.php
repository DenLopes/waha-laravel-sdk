<?php

declare(strict_types=1);

namespace DenLopes\Waha\Security;

use DenLopes\Waha\Contracts\ApiKeyProvider;
use DenLopes\Waha\Contracts\HostRegistry;

/**
 * Resolves API keys from host configuration.
 */
final class ConfigApiKeyProvider implements ApiKeyProvider
{
    public function __construct(private readonly HostRegistry $hosts) {}

    public function headerName(string $hostKey): string
    {
        return (string) ($this->hosts->get($hostKey)['api_key_header'] ?? 'X-Api-Key');
    }

    public function adminKey(string $hostKey): ?string
    {
        $key = $this->hosts->get($hostKey)['api_key'] ?? null;

        return is_string($key) && $key !== '' ? $key : null;
    }

    public function sessionKey(string $hostKey, string $sessionName): ?string
    {
        $keys = (array) ($this->hosts->get($hostKey)['session_keys'] ?? []);
        $key = $keys[$sessionName] ?? null;

        return is_string($key) && $key !== '' ? $key : null;
    }

    public function mode(string $hostKey): string
    {
        return (string) ($this->hosts->get($hostKey)['mode'] ?? 'admin_fallback');
    }
}
