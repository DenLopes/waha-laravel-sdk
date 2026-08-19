<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\WahaData;

/**
 * Payload of the `group.v2.update` webhook event.
 */
final readonly class GroupV2UpdateEventData extends WahaData
{
    /**
     * @param  array|null  $group  Updated group object.
     * @param  array|null  $raw  Raw event data.
     */
    public function __construct(
        public int $timestamp,
        public ?array $group,
        public ?array $raw,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            timestamp: (int) ($data['timestamp'] ?? 0),
            group: $data['group'] ?? null,
            raw: $data['_data'] ?? null,
        );
    }
}
