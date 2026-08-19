<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * A label reference used when assigning labels to a chat.
 */
final readonly class LabelIdData extends WahaData
{
    public function __construct(public string $id) {}

    public static function fromArray(array $data): static
    {
        return new self(id: (string) ($data['id'] ?? ''));
    }
}
