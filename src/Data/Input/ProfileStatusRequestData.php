<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * Payload for updating the profile status (About).
 */
final readonly class ProfileStatusRequestData extends WahaData
{
    public function __construct(public string $status) {}

    public static function fromArray(array $data): static
    {
        return new self(status: (string) ($data['status'] ?? ''));
    }
}
