<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * Payload for deleting a sent status.
 */
final readonly class DeleteStatusData extends WahaData
{
    /**
     * @param  string[]|null  $contacts  Contact list to delete the status for.
     */
    public function __construct(
        public ?string $id = null,
        public ?array $contacts = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: $data['id'] ?? null,
            contacts: $data['contacts'] ?? null,
        );
    }
}
