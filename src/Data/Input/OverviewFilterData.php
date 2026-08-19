<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * Filter for the chats overview endpoint.
 */
final readonly class OverviewFilterData extends WahaData
{
    /**
     * @param  string[]|null  $ids  Chat IDs to filter by.
     */
    public function __construct(public ?array $ids = null) {}

    public static function fromArray(array $data): static
    {
        return new self(ids: $data['ids'] ?? null);
    }
}
