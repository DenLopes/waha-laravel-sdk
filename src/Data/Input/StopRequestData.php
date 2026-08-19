<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * Payload for stopping (and restarting) the WAHA server.
 */
final readonly class StopRequestData extends WahaData
{
    public function __construct(public bool $force = false) {}

    public static function fromArray(array $data): static
    {
        return new self(force: (bool) ($data['force'] ?? false));
    }
}
