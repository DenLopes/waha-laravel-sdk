<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data;

final readonly class SettingsSecurityChangeInfo extends Data
{
    public function __construct(public bool $adminsOnly) {}

    /**
     * Create an instance from a raw WAHA API response array.
     *
     * @param  array  $data  Raw WAHA API response array.
     */
    public static function fromArray(array $data): static
    {
        return new self(adminsOnly: (bool) ($data['adminsOnly'] ?? true));
    }
}
