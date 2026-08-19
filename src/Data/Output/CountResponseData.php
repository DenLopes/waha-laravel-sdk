<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\WahaData;

final readonly class CountResponseData extends WahaData
{
    public function __construct(public int $count) {}

    /**
     * Create an instance from a raw WAHA API response array.
     *
     * @param  array  $data  Raw WAHA API response array.
     */
    public static function fromArray(array $data): static
    {
        return new self(count: (int) ($data['count'] ?? 0));
    }
}
