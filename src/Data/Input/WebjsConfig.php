<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * WebJS engine configuration.
 */
final readonly class WebjsConfig extends Data
{
    public function __construct(public ?bool $tagsEventsOn = null) {}

    public static function fromArray(array $data): static
    {
        return new self(tagsEventsOn: $data['tagsEventsOn'] ?? null);
    }
}
