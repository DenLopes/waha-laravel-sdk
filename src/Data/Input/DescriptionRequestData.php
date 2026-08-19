<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * Payload for updating a group description.
 */
final readonly class DescriptionRequestData extends WahaData
{
    public function __construct(public string $description) {}

    public static function fromArray(array $data): static
    {
        return new self(description: (string) ($data['description'] ?? ''));
    }
}
