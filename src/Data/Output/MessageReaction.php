<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\Data;
use DenLopes\Waha\Enums\MessageSource;

/**
 * Payload of the `message.reaction` webhook event.
 */
final readonly class MessageReaction extends Data
{
    public function __construct(
        public string $id,
        public int $timestamp,
        public ?string $from,
        public ?bool $fromMe,
        public ?MessageSource $source,
        public ?string $to,
        public ?string $participant,
        public ?Reaction $reaction,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            timestamp: (int) ($data['timestamp'] ?? 0),
            from: isset($data['from']) ? (string) $data['from'] : null,
            fromMe: isset($data['fromMe']) ? (bool) $data['fromMe'] : null,
            source: MessageSource::tryFrom((string) ($data['source'] ?? '')),
            to: isset($data['to']) ? (string) $data['to'] : null,
            participant: isset($data['participant']) ? (string) $data['participant'] : null,
            reaction: isset($data['reaction']) && is_array($data['reaction'])
                ? Reaction::fromArray($data['reaction'])
                : null,
        );
    }
}
