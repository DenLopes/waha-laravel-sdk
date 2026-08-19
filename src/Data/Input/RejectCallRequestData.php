<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * Payload for rejecting an incoming call.
 */
final readonly class RejectCallRequestData extends WahaData
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
