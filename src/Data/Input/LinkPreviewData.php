<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * A custom link preview payload.
 */
final readonly class LinkPreviewData extends WahaData
{
    /**
     * @param  LinkPreviewImageData|null  $image  Preview image.
     */
    public function __construct(
        public string $url,
        public string $title,
        public string $description,
        public ?LinkPreviewImageData $image = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            url: (string) ($data['url'] ?? ''),
            title: (string) ($data['title'] ?? ''),
            description: (string) ($data['description'] ?? ''),
            image: isset($data['image']) && is_array($data['image'])
                ? LinkPreviewImageData::fromArray($data['image'])
                : null,
        );
    }
}
