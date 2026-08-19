<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * A section of an interactive list message.
 */
final readonly class SectionData extends WahaData
{
    /**
     * @param  RowData[]  $rows
     */
    public function __construct(
        public string $title,
        public array $rows,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            title: (string) ($data['title'] ?? ''),
            rows: array_map(
                static fn (array $row) => RowData::fromArray($row),
                $data['rows'] ?? [],
            ),
        );
    }
}
