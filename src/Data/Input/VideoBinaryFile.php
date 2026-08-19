<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * A base64-encoded video file for the video-sending endpoints.
 */
final readonly class VideoBinaryFile extends Data
{
    public function __construct(
        public string $data,
        public string $mimetype = 'video/mp4',
        public string $filename = 'video.mp4',
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            data: (string) ($data['data'] ?? ''),
            mimetype: (string) ($data['mimetype'] ?? 'video/mp4'),
            filename: (string) ($data['filename'] ?? 'video.mp4'),
        );
    }
}
