<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * Payload for stopping (and restarting) the WAHA server.
 */
final readonly class StopRequest extends Data
{
    public function __construct(public bool $force = false) {}

    public static function fromArray(array $data): static
    {
        return new self(force: (bool) ($data['force'] ?? false));
    }
}
