<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data;

final readonly class SettingsMemberAddModeData extends WahaData
{
    public function __construct(public bool $membersCanAddNewMember) {}

    /**
     * Create an instance from a raw WAHA API response array.
     *
     * @param  array  $data  Raw WAHA API response array.
     */
    public static function fromArray(array $data): static
    {
        return new self(membersCanAddNewMember: (bool) ($data['membersCanAddNewMember'] ?? true));
    }
}
