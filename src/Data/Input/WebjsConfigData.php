<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * WebJS engine configuration.
 */
final readonly class WebjsConfigData extends WahaData
{
    public function __construct(public ?bool $tagsEventsOn = null) {}

    public static function fromArray(array $data): static
    {
        return new self(tagsEventsOn: $data['tagsEventsOn'] ?? null);
    }
}
