<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\WahaData;

final readonly class PasskeyChallengeData extends WahaData
{
    /**
     * @param  PasskeyAllowedCredentialData[]  $allowCredentials
     * @param  array|null  $extensions  WebAuthn extensions requested by WhatsApp.
     */
    public function __construct(
        public string $challenge,
        public int $timeout,
        public string $rpId,
        public array $allowCredentials,
        public string $userVerification,
        public ?array $extensions,
    ) {}

    /**
     * Create an instance from a raw WAHA API response array.
     *
     * @param  array  $data  Raw WAHA API response array.
     */
    public static function fromArray(array $data): static
    {
        return new self(
            challenge: (string) ($data['challenge'] ?? ''),
            timeout: (int) ($data['timeout'] ?? 0),
            rpId: (string) ($data['rpId'] ?? ''),
            allowCredentials: array_map(
                static fn (array $credential) => PasskeyAllowedCredentialData::fromArray($credential),
                $data['allowCredentials'] ?? [],
            ),
            userVerification: (string) ($data['userVerification'] ?? ''),
            extensions: $data['extensions'] ?? null,
        );
    }
}
