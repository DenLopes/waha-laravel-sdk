<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * An event (RSVP) message definition.
 */
final readonly class EventMessage extends Data
{
    public function __construct(
        public string $name,
        public int $startTime,
        public ?string $description = null,
        public ?int $endTime = null,
        public ?EventLocation $location = null,
        public ?bool $extraGuestsAllowed = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            name: (string) ($data['name'] ?? ''),
            startTime: (int) ($data['startTime'] ?? 0),
            description: $data['description'] ?? null,
            endTime: isset($data['endTime']) ? (int) $data['endTime'] : null,
            location: isset($data['location']) && is_array($data['location'])
                ? EventLocation::fromArray($data['location'])
                : null,
            extraGuestsAllowed: $data['extraGuestsAllowed'] ?? null,
        );
    }
}
