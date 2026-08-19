<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\WahaData;

final readonly class PasskeyAllowedCredentialData extends WahaData
{
    /**
     * @param  string[]|null  $transports  Authenticator transports the credential supports.
     */
    public function __construct(
        public string $id,
        public string $type,
        public ?array $transports,
    ) {}

    /**
     * Create an instance from a raw WAHA API response array.
     *
     * @param  array  $data  Raw WAHA API response array.
     */
    public static function fromArray(array $data): static
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            type: (string) ($data['type'] ?? ''),
            transports: $data['transports'] ?? null,
        );
    }
}
