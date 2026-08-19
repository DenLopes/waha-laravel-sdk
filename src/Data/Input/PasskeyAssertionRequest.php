<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * A WebAuthn public key credential assertion.
 */
final readonly class PasskeyAssertionRequest extends Data
{
    public function __construct(
        public string $id,
        public string $rawId,
        public string $type,
        public PasskeyAssertionResponse $response,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            rawId: (string) ($data['rawId'] ?? ''),
            type: (string) ($data['type'] ?? ''),
            response: PasskeyAssertionResponse::fromArray((array) ($data['response'] ?? [])),
        );
    }
}
