<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data;

use DenLopes\Waha\Contracts\HostRegistry;
use DenLopes\Waha\Enums\WahaApiKeyModeEnum;

/**
 * Immutable configuration for a single WAHA host.
 *
 * Built by the {@see HostRegistry} implementations from
 * either the `waha.hosts` config array or the `waha_hosts` database table.
 */
final readonly class WahaHostConfigData
{
    /**
     * @param  array<string, string>  $sessionKeys  Session name → API key map.
     */
    public function __construct(
        public string $baseUrl,
        public ?string $apiKey,
        public string $apiKeyHeader = 'X-Api-Key',
        public string $defaultSession = 'default',
        public WahaApiKeyModeEnum $mode = WahaApiKeyModeEnum::ADMIN_FALLBACK,
        public array $sessionKeys = [],
        public ?string $webhookSecret = null,
    ) {}

    /**
     * Build a host config from an associative array.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $sessionKeys = (array) ($data['session_keys'] ?? []);
        $sessionKeys = array_filter($sessionKeys, static fn (mixed $value): bool => is_string($value));

        return new self(
            baseUrl: (string) ($data['base_url'] ?? 'http://localhost:3000'),
            apiKey: isset($data['api_key']) && is_string($data['api_key']) && $data['api_key'] !== ''
                ? $data['api_key']
                : null,
            apiKeyHeader: (string) ($data['api_key_header'] ?? 'X-Api-Key'),
            defaultSession: (string) ($data['default_session'] ?? 'default'),
            mode: WahaApiKeyModeEnum::tryFrom((string) ($data['mode'] ?? '')) ?? WahaApiKeyModeEnum::ADMIN_FALLBACK,
            sessionKeys: $sessionKeys,
            webhookSecret: isset($data['webhook_secret']) && is_string($data['webhook_secret'])
                ? $data['webhook_secret']
                : null,
        );
    }
}
