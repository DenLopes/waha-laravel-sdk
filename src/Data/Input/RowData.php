<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * A row inside an interactive list section.
 */
final readonly class RowData extends WahaData
{
    public function __construct(
        public string $title,
        public string $rowId,
        public ?string $description = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            title: (string) ($data['title'] ?? ''),
            rowId: (string) ($data['rowId'] ?? ''),
            description: $data['description'] ?? null,
        );
    }
}
