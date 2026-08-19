<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * Payload for creating a group.
 */
final readonly class CreateGroupRequest extends Data
{
    /**
     * @param  Participant[]  $participants
     */
    public function __construct(
        public string $name,
        public array $participants,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            name: (string) ($data['name'] ?? ''),
            participants: array_map(
                static fn (array $participant) => Participant::fromArray($participant),
                $data['participants'] ?? [],
            ),
        );
    }
}
