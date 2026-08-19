<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * Payload for updating the profile name.
 */
final readonly class ProfileNameRequest extends Data
{
    public function __construct(public string $name) {}

    public static function fromArray(array $data): static
    {
        return new self(name: (string) ($data['name'] ?? ''));
    }
}
