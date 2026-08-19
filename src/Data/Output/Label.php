<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\Data;

final readonly class Label extends Data
{
    public function __construct(
        public string $id,
        public string $name,
        public int $color,
        public string $colorHex,
    ) {}

    /**
     * Create an instance from a raw WAHA API response array.
     *
     * @param  array  $data  Raw WAHA API response array.
     */
    public static function fromArray(array $data): static
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            name: (string) ($data['name'] ?? ''),
            color: (int) ($data['color'] ?? 0),
            colorHex: (string) ($data['colorHex'] ?? ''),
        );
    }
}
