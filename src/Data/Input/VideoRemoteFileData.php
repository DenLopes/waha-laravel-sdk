<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * A video file referenced by URL for the video-sending endpoints.
 */
final readonly class VideoRemoteFileData extends WahaData
{
    public function __construct(
        public string $url,
        public string $mimetype = 'video/mp4',
        public string $filename = 'video.mp4',
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            url: (string) ($data['url'] ?? ''),
            mimetype: (string) ($data['mimetype'] ?? 'video/mp4'),
            filename: (string) ($data['filename'] ?? 'video.mp4'),
        );
    }
}
