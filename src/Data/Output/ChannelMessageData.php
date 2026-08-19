<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\WahaData;

final readonly class ChannelMessageData extends WahaData
{
    /**
     * @param  array<string, int>  $reactions  Reaction emoji counts.
     */
    public function __construct(
        public array $reactions,
        public WAMessageData $message,
        public int $viewCount,
    ) {}

    /**
     * Create an instance from a raw WAHA API response array.
     *
     * @param  array  $data  Raw WAHA API response array.
     */
    public static function fromArray(array $data): static
    {
        return new self(
            reactions: (array) ($data['reactions'] ?? []),
            message: WAMessageData::fromArray((array) ($data['message'] ?? [])),
            viewCount: (int) ($data['viewCount'] ?? 0),
        );
    }
}
