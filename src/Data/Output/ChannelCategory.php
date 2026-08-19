<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\Data;

final readonly class ChannelCategory extends Data
{
    public function __construct(
        public string $value,
        public string $name,
    ) {}

    /**
     * Create an instance from a raw WAHA API response array.
     *
     * @param  array  $data  Raw WAHA API response array.
     */
    public static function fromArray(array $data): static
    {
        return new self(
            value: (string) ($data['value'] ?? ''),
            name: (string) ($data['name'] ?? ''),
        );
    }
}
