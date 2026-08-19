<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\WahaData;

/**
 * Contact information returned by the WAHA contacts endpoints.
 *
 * The OpenAPI document does not define a contact schema, so the well-known
 * fields are typed here and the full payload is preserved in {@see self::$raw}
 * for forward compatibility.
 */
final readonly class ContactInfoData extends WahaData
{
    /**
     * @param  array<string, mixed>  $raw  The complete, unmodified contact payload.
     */
    public function __construct(
        public string $id,
        public ?string $name = null,
        public ?string $pushName = null,
        public array $raw = [],
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            name: isset($data['name']) ? (string) $data['name'] : null,
            pushName: isset($data['pushName']) ? (string) $data['pushName'] : null,
            raw: $data,
        );
    }
}
