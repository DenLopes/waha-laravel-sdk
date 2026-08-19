<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\WahaData;

final readonly class ChannelListResultData extends WahaData
{
    /**
     * @param  ChannelPublicInfoData[]  $channels  Search result channels.
     */
    public function __construct(
        public ChannelPaginationData $page,
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
            page: ChannelPaginationData::fromArray((array) ($data['page'] ?? [])),
            channels: array_map(
                static fn (array $channel) => ChannelPublicInfoData::fromArray($channel),
                $data['channels'] ?? [],
            ),
        );
    }
}
