<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\Data;

final readonly class ReadChatMessages extends Data
{
    /**
     * @param  string[]  $ids  Messages IDs that have been read.
     */
    public function __construct(public array $ids) {}

    /**
     * Create an instance from a raw WAHA API response array.
     *
     * @param  array  $data  Raw WAHA API response array.
     */
    public static function fromArray(array $data): static
    {
        return new self(ids: (array) ($data['ids'] ?? []));
    }
}
