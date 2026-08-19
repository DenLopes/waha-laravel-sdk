<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\Data;

/**
 * Payload of the `group.v2.leave` webhook event.
 */
final readonly class GroupV2LeaveEvent extends Data
{
    /**
     * @param  array|null  $raw  Raw event data.
     */
    public function __construct(
        public int $timestamp,
        public ?GroupId $group,
        public ?array $raw,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            timestamp: (int) ($data['timestamp'] ?? 0),
            group: isset($data['group']) && is_array($data['group'])
                ? GroupId::fromArray($data['group'])
                : null,
            raw: $data['_data'] ?? null,
        );
    }
}
