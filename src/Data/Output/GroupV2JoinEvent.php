<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\Data;

/**
 * Payload of the `group.v2.join` webhook event.
 */
final readonly class GroupV2JoinEvent extends Data
{
    /**
     * @param  array|null  $raw  Raw event data.
     */
    public function __construct(
        public int $timestamp,
        public ?GroupInfo $group,
        public ?array $raw,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            timestamp: (int) ($data['timestamp'] ?? 0),
            group: isset($data['group']) && is_array($data['group'])
                ? GroupInfo::fromArray($data['group'])
                : null,
            raw: $data['_data'] ?? null,
        );
    }
}
