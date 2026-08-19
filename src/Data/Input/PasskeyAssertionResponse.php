<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * WebAuthn assertion response data.
 */
final readonly class PasskeyAssertionResponse extends Data
{
    public function __construct(
        public string $clientDataJSON,
        public string $authenticatorData,
        public string $signature,
        public ?string $userHandle = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            clientDataJSON: (string) ($data['clientDataJSON'] ?? ''),
            authenticatorData: (string) ($data['authenticatorData'] ?? ''),
            signature: (string) ($data['signature'] ?? ''),
            userHandle: $data['userHandle'] ?? null,
        );
    }
}
