<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * A group participant referenced by ID.
 */
final readonly class Participant extends Data
{
    public function __construct(public string $id) {}

    public static function fromArray(array $data): static
    {
        return new self(id: (string) ($data['id'] ?? ''));
    }
}
