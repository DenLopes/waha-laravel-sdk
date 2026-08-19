<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\Data;

/**
 * Media object attached to a WhatsApp message.
 */
final readonly class Media extends Data
{
    /**
     * @param  array|null  $error  Error payload if the media failed to download.
     */
    public function __construct(
        public ?string $url,
        public ?string $mimetype,
        public ?string $filename,
        public ?S3Media $s3,
        public ?array $error,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            url: isset($data['url']) ? (string) $data['url'] : null,
            mimetype: isset($data['mimetype']) ? (string) $data['mimetype'] : null,
            filename: isset($data['filename']) ? (string) $data['filename'] : null,
            s3: isset($data['s3']) && is_array($data['s3'])
                ? S3Media::fromArray($data['s3'])
                : null,
            error: $data['error'] ?? null,
        );
    }
}
