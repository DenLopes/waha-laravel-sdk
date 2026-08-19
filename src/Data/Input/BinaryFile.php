<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * A base64-encoded media file.
 */
final readonly class BinaryFile extends Data
{
    public function __construct(
        public string $mimetype,
        public string $data,
        public ?string $filename = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            mimetype: (string) ($data['mimetype'] ?? ''),
            data: (string) ($data['data'] ?? ''),
            filename: $data['filename'] ?? null,
        );
    }
}
