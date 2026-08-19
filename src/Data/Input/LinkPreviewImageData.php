<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * Image for a custom link preview.
 *
 * Provide either a `url` (file content fetched by WAHA) or a base64 `data`
 * payload, but not both.
 */
final readonly class LinkPreviewImageData extends WahaData
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
