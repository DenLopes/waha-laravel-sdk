<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * A group participant referenced by ID.
 */
final readonly class ParticipantData extends WahaData
{
    public function __construct(public string $id) {}

    public static function fromArray(array $data): static
    {
        return new self(id: (string) ($data['id'] ?? ''));
    }
}
