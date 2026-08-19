<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * A WebAuthn public key credential assertion.
 */
final readonly class PasskeyAssertionRequestData extends WahaData
{
    public function __construct(
        public string $id,
        public string $rawId,
        public string $type,
        public PasskeyAssertionResponseData $response,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            rawId: (string) ($data['rawId'] ?? ''),
            type: (string) ($data['type'] ?? ''),
            response: PasskeyAssertionResponseData::fromArray((array) ($data['response'] ?? [])),
        );
    }
}
