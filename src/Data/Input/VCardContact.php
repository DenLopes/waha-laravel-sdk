<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * A raw vCard string contact.
 */
final readonly class VCardContact extends Data
{
    public function __construct(public string $vcard) {}

    public static function fromArray(array $data): static
    {
        return new self(vcard: (string) ($data['vcard'] ?? ''));
    }
}
