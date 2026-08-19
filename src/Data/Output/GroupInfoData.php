<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\WahaData;

/**
 * Full group information returned by the groups API.
 */
final readonly class GroupInfoData extends WahaData
{
    /**
     * @param  GroupParticipantData[]  $participants
     */
    public function __construct(
        public string $id,
        public ?string $subject,
        public ?string $description,
        public ?string $invite,
        public ?bool $membersCanAddNewMember,
        public ?bool $membersCanSendMessages,
        public ?bool $newMembersApprovalRequired,
        public array $participants,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            subject: isset($data['subject']) ? (string) $data['subject'] : null,
            description: isset($data['description']) ? (string) $data['description'] : null,
            invite: isset($data['invite']) ? (string) $data['invite'] : null,
            membersCanAddNewMember: isset($data['membersCanAddNewMember']) ? (bool) $data['membersCanAddNewMember'] : null,
            membersCanSendMessages: isset($data['membersCanSendMessages']) ? (bool) $data['membersCanSendMessages'] : null,
            newMembersApprovalRequired: isset($data['newMembersApprovalRequired']) ? (bool) $data['newMembersApprovalRequired'] : null,
            participants: array_map(
                static fn (array $participant) => GroupParticipantData::fromArray($participant),
                $data['participants'] ?? [],
            ),
        );
    }
}
