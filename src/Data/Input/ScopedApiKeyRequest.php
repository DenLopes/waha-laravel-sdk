<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * Payload for creating a media-download-only or control-only API key.
 */
final readonly class ScopedApiKeyRequest extends Data
{
    public function __construct(public string $session) {}

    public static function fromArray(array $data): static
    {
        return new self(session: (string) ($data['session'] ?? ''));
    }
}
