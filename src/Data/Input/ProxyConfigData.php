<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * Proxy configuration for a session.
 */
final readonly class ProxyConfigData extends WahaData
{
    public function __construct(
        public string $server,
        public ?string $username = null,
        public ?string $password = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            server: (string) ($data['server'] ?? ''),
            username: $data['username'] ?? null,
            password: $data['password'] ?? null,
        );
    }
}
