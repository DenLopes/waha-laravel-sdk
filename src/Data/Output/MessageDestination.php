<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\Data;

/**
 * Message destination metadata (shared by poll vote and event response payloads).
 */
final readonly class MessageDestination extends Data
{
    public function __construct(
        public string $id,
        public ?string $to,
        public ?string $from,
        public ?bool $fromMe,
        public ?string $participant,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            to: $data['to'] ?? null,
            from: $data['from'] ?? null,
            fromMe: $data['fromMe'] ?? null,
            participant: $data['participant'] ?? null,
        );
    }
}
