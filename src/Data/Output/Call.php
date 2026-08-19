<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\Data;

/**
 * Payload of the `call.received`, `call.accepted` and `call.rejected` webhook events.
 */
final readonly class Call extends Data
{
    /**
     * @param  array|null  $raw  Raw call data.
     */
    public function __construct(
        public string $id,
        public ?string $from,
        public int $timestamp,
        public bool $isVideo,
        public bool $isGroup,
        public ?array $raw,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            from: $data['from'] ?? null,
            timestamp: (int) ($data['timestamp'] ?? 0),
            isVideo: (bool) ($data['isVideo'] ?? false),
            isGroup: (bool) ($data['isGroup'] ?? false),
            raw: $data['_data'] ?? null,
        );
    }
}
