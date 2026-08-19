<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * GOWS engine configuration.
 */
final readonly class GowsConfig extends Data
{
    public function __construct(public ?GowsStorageConfig $storage = null) {}

    public static function fromArray(array $data): static
    {
        return new self(
            storage: isset($data['storage']) && is_array($data['storage'])
                ? GowsStorageConfig::fromArray($data['storage'])
                : null,
        );
    }
}
