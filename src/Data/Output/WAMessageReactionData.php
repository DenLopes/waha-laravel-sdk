<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\WahaData;
use DenLopes\Waha\Enums\WahaMessageSourceEnum;

/**
 * Payload of the `message.reaction` webhook event.
 */
final readonly class WAMessageReactionData extends WahaData
{
    public function __construct(
        public string $id,
        public int $timestamp,
        public ?string $from,
        public ?bool $fromMe,
        public ?WahaMessageSourceEnum $source,
        public ?string $to,
        public ?string $participant,
        public ?WAReactionData $reaction,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            timestamp: (int) ($data['timestamp'] ?? 0),
            from: isset($data['from']) ? (string) $data['from'] : null,
            fromMe: isset($data['fromMe']) ? (bool) $data['fromMe'] : null,
            source: WahaMessageSourceEnum::tryFrom((string) ($data['source'] ?? '')),
            to: isset($data['to']) ? (string) $data['to'] : null,
            participant: isset($data['participant']) ? (string) $data['participant'] : null,
            reaction: isset($data['reaction']) && is_array($data['reaction'])
                ? WAReactionData::fromArray($data['reaction'])
                : null,
        );
    }
}
