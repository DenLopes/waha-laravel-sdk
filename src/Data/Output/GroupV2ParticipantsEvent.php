<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\Data;
use DenLopes\Waha\Enums\GroupParticipantEventType;

/**
 * Payload of the `group.v2.participants` webhook event.
 */
final readonly class GroupV2ParticipantsEvent extends Data
{
    /**
     * @param  GroupParticipant[]  $participants
     * @param  array|null  $raw  Raw event data.
     */
    public function __construct(
        public ?GroupParticipantEventType $type,
        public int $timestamp,
        public ?GroupId $group,
        public array $participants,
        public ?array $raw,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            type: GroupParticipantEventType::tryFrom((string) ($data['type'] ?? '')),
            timestamp: (int) ($data['timestamp'] ?? 0),
            group: isset($data['group']) && is_array($data['group'])
                ? GroupId::fromArray($data['group'])
                : null,
            participants: array_map(
                static fn (array $participant) => GroupParticipant::fromArray($participant),
                $data['participants'] ?? [],
            ),
            raw: $data['_data'] ?? null,
        );
    }
}
