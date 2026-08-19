<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * A voice file referenced by URL for the voice-sending endpoints.
 *
 * WAHA expects the opus MIME type by default; override it only when the remote
 * file is already in the required format.
 */
final readonly class VoiceRemoteFileData extends WahaData
{
    public function __construct(
        public string $url,
        public string $mimetype = 'audio/ogg; codecs=opus',
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            url: (string) ($data['url'] ?? ''),
            mimetype: (string) ($data['mimetype'] ?? 'audio/ogg; codecs=opus'),
        );
    }
}
