<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * NOWEB engine configuration.
 */
final readonly class NowebConfigData extends WahaData
{
    public function __construct(
        public bool $markOnline = true,
        public ?NowebStoreConfigData $store = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            markOnline: (bool) ($data['markOnline'] ?? true),
            store: isset($data['store']) && is_array($data['store'])
                ? NowebStoreConfigData::fromArray($data['store'])
                : null,
        );
    }
}
