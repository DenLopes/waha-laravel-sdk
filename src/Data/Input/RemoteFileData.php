<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * A media file referenced by URL.
 *
 * Used by image/file/voice/video/picture endpoints where WAHA downloads the
 * file itself.
 */
final readonly class RemoteFileData extends WahaData
{
    public function __construct(
        public string $mimetype,
        public string $url,
        public ?string $filename = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            mimetype: (string) ($data['mimetype'] ?? ''),
            url: (string) ($data['url'] ?? ''),
            filename: $data['filename'] ?? null,
        );
    }
}
