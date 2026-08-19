<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\Data;
use DenLopes\Waha\Enums\GroupParticipantRole;

final readonly class GroupParticipant extends Data
{
    public function __construct(
        public string $id,
        public ?string $pn,
        public ?GroupParticipantRole $role,
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
            pn: $data['pn'] ?? null,
            role: GroupParticipantRole::tryFrom((string) ($data['role'] ?? '')),
        );
    }
}
