<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\Data;
use DenLopes\Waha\Enums\PresenceStatus;

final readonly class Presence extends Data
{
    public function __construct(
        public string $participant,
        public ?int $lastSeen,
        public ?PresenceStatus $lastKnownPresence,
    ) {}

    /**
     * Create an instance from a raw WAHA API response array.
     *
     * @param  array  $data  Raw WAHA API response array.
     */
    public static function fromArray(array $data): static
    {
        return new self(
            participant: (string) ($data['participant'] ?? ''),
            lastSeen: isset($data['lastSeen']) ? (int) $data['lastSeen'] : null,
            lastKnownPresence: PresenceStatus::tryFrom((string) ($data['lastKnownPresence'] ?? '')),
        );
    }
}
