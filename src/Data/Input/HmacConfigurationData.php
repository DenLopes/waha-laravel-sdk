<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * HMAC signing configuration for webhook payloads.
 */
final readonly class HmacConfigurationData extends WahaData
{
    public function __construct(public string $key) {}

    public static function fromArray(array $data): static
    {
        return new self(key: (string) ($data['key'] ?? ''));
    }
}
