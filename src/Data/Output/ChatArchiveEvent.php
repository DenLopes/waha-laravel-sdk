<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\Data;

/**
 * Payload of the `chat.archive` webhook event.
 */
final readonly class ChatArchiveEvent extends Data
{
    public function __construct(
        public string $id,
        public bool $archived,
        public int $timestamp,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            archived: (bool) ($data['archived'] ?? false),
            timestamp: (int) ($data['timestamp'] ?? 0),
        );
    }
}
