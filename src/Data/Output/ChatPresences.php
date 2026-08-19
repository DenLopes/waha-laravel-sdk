<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\Data;

final readonly class ChatPresences extends Data
{
    /**
     * @param  Presence[]  $presences
     */
    public function __construct(
        public string $id,
        public array $presences,
    ) {}

    /**
     * Create an instance from a raw WAHA API response array.
     *
     * @param  array  $data  Raw WAHA API response array.
     */
    public static function fromArray(array $data): static
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            presences: array_map(
                static fn (array $presence) => Presence::fromArray($presence),
                $data['presences'] ?? [],
            ),
        );
    }
}
