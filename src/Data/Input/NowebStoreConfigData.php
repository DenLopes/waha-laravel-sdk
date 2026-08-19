<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * NOWEB engine storage settings.
 */
final readonly class NowebStoreConfigData extends WahaData
{
    public function __construct(
        public bool $enabled = false,
        public bool $fullSync = false,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            enabled: (bool) ($data['enabled'] ?? false),
            fullSync: (bool) ($data['fullSync'] ?? false),
        );
    }
}
