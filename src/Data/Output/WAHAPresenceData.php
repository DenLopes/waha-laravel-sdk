<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\WahaData;
use DenLopes\Waha\Enums\WahaPresenceEnum;

final readonly class WAHAPresenceData extends WahaData
{
    public function __construct(
        public string $participant,
        public ?int $lastSeen,
        public ?WahaPresenceEnum $lastKnownPresence,
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
            lastKnownPresence: WahaPresenceEnum::tryFrom((string) ($data['lastKnownPresence'] ?? '')),
        );
    }
}
