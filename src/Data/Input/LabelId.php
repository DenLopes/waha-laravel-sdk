<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * A label reference used when assigning labels to a chat.
 */
final readonly class LabelId extends Data
{
    public function __construct(public string $id) {}

    public static function fromArray(array $data): static
    {
        return new self(id: (string) ($data['id'] ?? ''));
    }
}
