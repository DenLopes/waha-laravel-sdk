<?php

declare(strict_types=1);

namespace DenLopes\Waha\Registry;

use DenLopes\Waha\Contracts\HostRegistry;
use DenLopes\Waha\Data\HostConfig;
use DenLopes\Waha\Exceptions\UnknownHostException;
use DenLopes\Waha\Models\Host;

/**
 * Reads host definitions from the `waha_hosts` table.
 */
final class DbHostRegistry implements HostRegistry
{
    public function get(string $hostKey): HostConfig
    {
        $hosts = $this->all();

        if (!isset($hosts[$hostKey])) {
            throw new UnknownHostException("Unknown WAHA host: {$hostKey}");
        }

        return $hosts[$hostKey];
    }

    /**
     * @return array<string, HostConfig>
     */
    public function all(): array
    {
        return Host::query()
            ->where('is_active', true)
            ->get()
            ->mapWithKeys(fn (Host $host): array => [$host->key => HostConfig::fromArray([
                'base_url'        => $host->base_url,
                'api_key'         => $host->api_key,
                'api_key_header'  => $host->api_key_header,
                'default_session' => $host->default_session,
                'mode'            => $host->mode,
                'session_keys'    => $host->session_keys ?? [],
                'webhook_secret'  => $host->webhook_secret,
            ])])
            ->all();
    }

    public function exists(string $hostKey): bool
    {
        return Host::query()
            ->where('key', $hostKey)
            ->where('is_active', true)
            ->exists();
    }
}
