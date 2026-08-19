<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * A custom HTTP header attached to webhook deliveries.
 */
final readonly class CustomHeaderData extends WahaData
{
    public function __construct(
        public string $name,
        public string $value,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            name: (string) ($data['name'] ?? ''),
            value: (string) ($data['value'] ?? ''),
        );
    }
}
