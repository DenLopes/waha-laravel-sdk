<?php

declare(strict_types=1);

namespace DenLopes\Waha\Registry;

use DenLopes\Waha\Contracts\HostRegistry;
use DenLopes\Waha\Data\HostConfig;
use DenLopes\Waha\Exceptions\UnknownHostException;

/**
 * Reads host definitions from `config('waha.hosts')`.
 *
 * When no hosts are configured, it falls back to the legacy single-host keys
 * (`waha.base_url`, `waha.api_key`, `waha.default_session`) as the `primary` host.
 */
final class ConfigHostRegistry implements HostRegistry
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
        $hosts = (array) config('waha.hosts', []);

        if ($hosts !== []) {
            return array_map(
                static fn (array $host): HostConfig => HostConfig::fromArray($host),
                $hosts,
            );
        }

        return [
            'primary' => HostConfig::fromArray([
                'base_url'        => (string) config('waha.base_url', 'http://localhost:3000'),
                'api_key'         => config('waha.api_key'),
                'default_session' => (string) config('waha.default_session', 'default'),
            ]),
        ];
    }

    public function exists(string $hostKey): bool
    {
        return isset($this->all()[$hostKey]);
    }
}
