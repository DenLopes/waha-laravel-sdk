<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * How a connected session renders as a device (browser + device name).
 */
final readonly class ClientSessionConfig extends Data
{
    public function __construct(
        public ?string $deviceName = null,
        public ?string $browserName = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            deviceName: $data['deviceName'] ?? null,
            browserName: $data['browserName'] ?? null,
        );
    }
}
