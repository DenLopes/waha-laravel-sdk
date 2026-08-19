<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * GOWS engine configuration.
 */
final readonly class GowsConfigData extends WahaData
{
    public function __construct(public ?GowsStorageConfigData $storage = null) {}

    public static function fromArray(array $data): static
    {
        return new self(
            storage: isset($data['storage']) && is_array($data['storage'])
                ? GowsStorageConfigData::fromArray($data['storage'])
                : null,
        );
    }
}
