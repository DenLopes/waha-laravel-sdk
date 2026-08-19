<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * Body for the POST chats overview endpoint.
 */
final readonly class OverviewBodyRequestData extends WahaData
{
    public function __construct(
        public GetChatsOverviewParamsData $pagination,
        public ?OverviewFilterData $filter = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            pagination: GetChatsOverviewParamsData::fromArray((array) ($data['pagination'] ?? [])),
            filter: isset($data['filter']) && is_array($data['filter'])
                ? OverviewFilterData::fromArray($data['filter'])
                : null,
        );
    }
}
