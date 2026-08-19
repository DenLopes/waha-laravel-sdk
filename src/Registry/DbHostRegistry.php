<?php

declare(strict_types=1);

namespace DenLopes\Waha\Registry;

use DenLopes\Waha\Contracts\HostRegistry;
use DenLopes\Waha\Exception\UnknownHostException;
use DenLopes\Waha\Models\WahaHost;

/**
 * Reads host definitions from the `waha_hosts` table.
 */
final class DbHostRegistry implements HostRegistry
{
    public function get(string $hostKey): array
    {
        $hosts = $this->all();

        if (! isset($hosts[$hostKey])) {
            throw new UnknownHostException("Unknown WAHA host: {$hostKey}");
        }

        return $hosts[$hostKey];
    }

    public function all(): array
    {
        return WahaHost::query()
            ->where('is_active', true)
            ->get()
            ->mapWithKeys(fn (WahaHost $host): array => [$host->key => [
                'base_url'        => $host->base_url,
                'api_key'         => $host->api_key,
                'api_key_header'  => $host->api_key_header,
                'default_session' => $host->default_session,
                'mode'            => $host->mode,
                'session_keys'    => $host->session_keys ?? [],
                'webhook_secret'  => $host->webhook_secret,
            ]])
            ->all();
    }

    public function exists(string $hostKey): bool
    {
        return WahaHost::query()
            ->where('key', $hostKey)
            ->where('is_active', true)
            ->exists();
    }
}
