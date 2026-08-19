<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * Payload for requesting a one-time registration code.
 */
final readonly class RequestCodeRequest extends Data
{
    public function __construct(
        public string $phoneNumber,
        public ?string $method = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            phoneNumber: (string) ($data['phoneNumber'] ?? ''),
            method: self::string($data, 'method'),
        );
    }
}
