<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * Payload for creating or updating a contact in the address book.
 */
final readonly class ContactUpdateBody extends Data
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
