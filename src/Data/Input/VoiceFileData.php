<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * Voice file to convert to the WhatsApp (opus) format.
 */
final readonly class VoiceFileData extends WahaData
{
    public function __construct(
        public ?string $url = null,
        public ?string $data = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            url: $data['url'] ?? null,
            data: $data['data'] ?? null,
        );
    }
}
