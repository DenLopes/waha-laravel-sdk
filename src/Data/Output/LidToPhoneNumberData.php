<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\WahaData;

final readonly class LidToPhoneNumberData extends WahaData
{
    public function __construct(
        public ?string $lid,
        public ?string $pn,
    ) {}

    /**
     * Create an instance from a raw WAHA API response array.
     *
     * @param  array  $data  Raw WAHA API response array.
     */
    public static function fromArray(array $data): static
    {
        return new self(
            lid: $data['lid'] ?? null,
            pn: $data['pn'] ?? null,
        );
    }
}
