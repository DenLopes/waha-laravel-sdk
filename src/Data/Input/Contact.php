<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * A contact to share as a vCard.
 *
 * `vcard` is nullable on purpose: when omitted, WAHA derives the vCard from the
 * other fields. The OpenAPI document marks it required but also gives it a
 * `null` default, so treating it as optional matches the real contract.
 */
final readonly class Contact extends Data
{
    /**
     * @param  string  $fullName  The full name of the contact.
     * @param  string  $phoneNumber  The phone number of the contact.
     * @param  string|null  $organization  The organization of the contact.
     * @param  string|null  $whatsappId  The WhatsApp ID (without + or @c.us).
     * @param  string|null  $vcard  Explicit vCard string, or null to auto-generate.
     */
    public function __construct(
        public string $fullName,
        public string $phoneNumber,
        public ?string $organization = null,
        public ?string $whatsappId = null,
        public ?string $vcard = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            fullName: (string) ($data['fullName'] ?? ''),
            phoneNumber: (string) ($data['phoneNumber'] ?? ''),
            organization: $data['organization'] ?? null,
            whatsappId: $data['whatsappId'] ?? null,
            vcard: $data['vcard'] ?? null,
        );
    }
}
