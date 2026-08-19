<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * A base64-encoded voice file for the voice-sending endpoints.
 */
final readonly class VoiceBinaryFile extends Data
{
    public function __construct(
        public string $data,
        public string $mimetype = 'audio/ogg; codecs=opus',
        public string $filename = 'voice-message.mp3',
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            data: (string) ($data['data'] ?? ''),
            mimetype: (string) ($data['mimetype'] ?? 'audio/ogg; codecs=opus'),
            filename: (string) ($data['filename'] ?? 'voice-message.mp3'),
        );
    }
}
