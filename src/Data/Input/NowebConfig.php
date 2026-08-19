<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * NOWEB engine configuration.
 */
final readonly class NowebConfig extends Data
{
    public function __construct(
        public bool $markOnline = true,
        public ?NowebStoreConfig $store = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            markOnline: (bool) ($data['markOnline'] ?? true),
            store: isset($data['store']) && is_array($data['store'])
                ? NowebStoreConfig::fromArray($data['store'])
                : null,
        );
    }
}
