<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * Body for the POST chats overview endpoint.
 */
final readonly class OverviewBodyRequest extends Data
{
    public function __construct(
        public GetChatsOverviewParams $pagination,
        public ?OverviewFilter $filter = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            pagination: GetChatsOverviewParams::fromArray((array) ($data['pagination'] ?? [])),
            filter: isset($data['filter']) && is_array($data['filter'])
                ? OverviewFilter::fromArray($data['filter'])
                : null,
        );
    }
}
