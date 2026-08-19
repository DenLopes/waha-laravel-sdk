<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\Data;

final readonly class ChannelPagination extends Data
{
    public function __construct(
        public ?string $startCursor,
        public ?string $endCursor,
        public bool $hasNextPage,
        public bool $hasPreviousPage,
    ) {}

    /**
     * Create an instance from a raw WAHA API response array.
     *
     * @param  array  $data  Raw WAHA API response array.
     */
    public static function fromArray(array $data): static
    {
        return new self(
            startCursor: $data['startCursor'] ?? null,
            endCursor: $data['endCursor'] ?? null,
            hasNextPage: (bool) ($data['hasNextPage'] ?? false),
            hasPreviousPage: (bool) ($data['hasPreviousPage'] ?? false),
        );
    }
}
