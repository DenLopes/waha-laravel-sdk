<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\WahaData;
use DenLopes\Waha\Enums\WahaGroupParticipantEventTypeEnum;

/**
 * Payload of the `group.v2.participants` webhook event.
 */
final readonly class GroupV2ParticipantsEventData extends WahaData
{
    /**
     * @param  GroupParticipantData[]  $participants
     * @param  array|null  $raw  Raw event data.
     */
    public function __construct(
        public ?WahaGroupParticipantEventTypeEnum $type,
        public int $timestamp,
        public ?GroupIdData $group,
        public array $participants,
        public ?array $raw,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            type: WahaGroupParticipantEventTypeEnum::tryFrom((string) ($data['type'] ?? '')),
            timestamp: (int) ($data['timestamp'] ?? 0),
            group: isset($data['group']) && is_array($data['group'])
                ? GroupIdData::fromArray($data['group'])
                : null,
            participants: array_map(
                static fn (array $participant) => GroupParticipantData::fromArray($participant),
                $data['participants'] ?? [],
            ),
            raw: $data['_data'] ?? null,
        );
    }
}
