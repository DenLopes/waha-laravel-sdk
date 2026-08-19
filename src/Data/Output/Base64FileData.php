<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\WahaData;

final readonly class Base64FileData extends WahaData
{
    public function __construct(
        public string $mimetype,
        public string $data,
    ) {}

    /**
     * Create an instance from a raw WAHA API response array.
     *
     * @param  array  $data  Raw WAHA API response array.
     */
    public static function fromArray(array $data): static
    {
        return new self(
            mimetype: (string) ($data['mimetype'] ?? ''),
            data: (string) ($data['data'] ?? ''),
        );
    }
}
