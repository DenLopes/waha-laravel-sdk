<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * Location attached to an event message.
 */
final readonly class EventLocationData extends WahaData
{
    public function __construct(public string $name) {}

    public static function fromArray(array $data): static
    {
        return new self(name: (string) ($data['name'] ?? ''));
    }
}
