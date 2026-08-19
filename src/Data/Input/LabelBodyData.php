<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * Payload for creating or updating a label.
 */
final readonly class LabelBodyData extends WahaData
{
    public function __construct(
        public string $name,
        public ?string $colorHex = null,
        public ?int $color = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            name: (string) ($data['name'] ?? ''),
            colorHex: $data['colorHex'] ?? null,
            color: isset($data['color']) ? (int) $data['color'] : null,
        );
    }
}
