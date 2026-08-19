<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * A section of an interactive list message.
 */
final readonly class Section extends Data
{
    /**
     * @param  Row[]  $rows
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
                static fn (array $row) => Row::fromArray($row),
                $data['rows'] ?? [],
            ),
        );
    }
}
