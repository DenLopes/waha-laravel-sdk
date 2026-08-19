<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * Payload for rejecting an incoming call.
 */
final readonly class RejectCallRequest extends Data
{
    public function __construct(
        public string $from,
        public string $id,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            from: (string) ($data['from'] ?? ''),
            id: (string) ($data['id'] ?? ''),
        );
    }
}
