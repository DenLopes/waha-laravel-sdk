<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * Video file to convert to the WhatsApp (mp4) format.
 */
final readonly class VideoFile extends Data
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
