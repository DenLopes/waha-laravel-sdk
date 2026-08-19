<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\WahaData;

final readonly class ReadChatMessagesData extends WahaData
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
