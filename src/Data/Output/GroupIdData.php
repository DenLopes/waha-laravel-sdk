<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\WahaData;

/**
 * A group referenced only by ID.
 */
final readonly class GroupIdData extends WahaData
{
    public function __construct(public string $id) {}

    public static function fromArray(array $data): static
    {
        return new self(id: (string) ($data['id'] ?? ''));
    }
}
