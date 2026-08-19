<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\Data;

/**
 * A single poll vote.
 */
final readonly class PollVote extends Data
{
    /**
     * @param  string[]  $selectedOptions
     */
    public function __construct(
        public string $id,
        public array $selectedOptions,
        public int $timestamp,
        public ?string $to,
        public ?string $from,
        public ?bool $fromMe,
        public ?string $participant,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            selectedOptions: (array) ($data['selectedOptions'] ?? []),
            timestamp: (int) ($data['timestamp'] ?? 0),
            to: $data['to'] ?? null,
            from: $data['from'] ?? null,
            fromMe: $data['fromMe'] ?? null,
            participant: $data['participant'] ?? null,
        );
    }
}
