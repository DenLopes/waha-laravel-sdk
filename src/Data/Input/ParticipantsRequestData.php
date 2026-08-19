<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * Payload for adding/removing/promoting/demoting group participants.
 */
final readonly class ParticipantsRequestData extends WahaData
{
    /**
     * @param  ParticipantData[]  $participants
     */
    public function __construct(public array $participants) {}

    public static function fromArray(array $data): static
    {
        return new self(
            participants: array_map(
                static fn (array $participant) => ParticipantData::fromArray($participant),
                $data['participants'] ?? [],
            ),
        );
    }
}
