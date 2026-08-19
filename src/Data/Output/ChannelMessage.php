<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\Data;

final readonly class ChannelMessage extends Data
{
    /**
     * @param  array<string, int>  $reactions  Reaction emoji counts.
     */
    public function __construct(
        public array $reactions,
        public MessageData $message,
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
            message: MessageData::fromArray((array) ($data['message'] ?? [])),
            viewCount: (int) ($data['viewCount'] ?? 0),
        );
    }
}
