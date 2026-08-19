<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * A custom link preview payload.
 */
final readonly class LinkPreview extends Data
{
    /**
     * @param  LinkPreviewImage|null  $image  Preview image.
     */
    public function __construct(
        public string $url,
        public string $title,
        public string $description,
        public ?LinkPreviewImage $image = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            url: (string) ($data['url'] ?? ''),
            title: (string) ($data['title'] ?? ''),
            description: (string) ($data['description'] ?? ''),
            image: isset($data['image']) && is_array($data['image'])
                ? LinkPreviewImage::fromArray($data['image'])
                : null,
        );
    }
}
