<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * Pagination parameters for the chats overview endpoint.
 */
final readonly class GetChatsOverviewParamsData extends WahaData
{
    public function __construct(
        public bool $merge = true,
        public ?int $limit = 20,
        public ?int $offset = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            merge: (bool) ($data['merge'] ?? true),
            limit: isset($data['limit']) ? (int) $data['limit'] : 20,
            offset: isset($data['offset']) ? (int) $data['offset'] : null,
        );
    }
}
