<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\Data;

final readonly class ChannelListResult extends Data
{
    /**
     * @param  ChannelPublicInfo[]  $channels  Search result channels.
     */
    public function __construct(
        public ChannelPagination $page,
        public array $channels,
    ) {}

    /**
     * Create an instance from a raw WAHA API response array.
     *
     * @param  array  $data  Raw WAHA API response array.
     */
    public static function fromArray(array $data): static
    {
        return new self(
            page: ChannelPagination::fromArray((array) ($data['page'] ?? [])),
            channels: array_map(
                static fn (array $channel) => ChannelPublicInfo::fromArray($channel),
                $data['channels'] ?? [],
            ),
        );
    }
}
