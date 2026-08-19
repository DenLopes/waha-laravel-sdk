<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * Payload for creating or updating a contact in the address book.
 */
final readonly class ContactUpdateBodyData extends WahaData
{
    public function __construct(
        public string $firstName,
        public string $lastName,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            firstName: (string) ($data['firstName'] ?? ''),
            lastName: (string) ($data['lastName'] ?? ''),
        );
    }
}
