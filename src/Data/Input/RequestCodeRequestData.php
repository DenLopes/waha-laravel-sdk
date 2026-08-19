<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * Payload for requesting a one-time registration code.
 */
final readonly class RequestCodeRequestData extends WahaData
{
    public function __construct(
        public string $phoneNumber,
        public ?string $method = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            phoneNumber: (string) ($data['phoneNumber'] ?? ''),
            method: $data['method'] ?? null,
        );
    }
}
