<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * Payload for voting on a poll.
 */
final readonly class MessagePollVoteRequest extends Data
{
    /**
     * @param  array<int, array<int, string>>  $votes  Poll options being voted for (list of option lists).
     */
    public function __construct(
        public string $chatId,
        public string $pollMessageId,
        public array $votes,
        public ?int $pollServerId = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            chatId: (string) ($data['chatId'] ?? ''),
            pollMessageId: (string) ($data['pollMessageId'] ?? ''),
            votes: (array) ($data['votes'] ?? []),
            pollServerId: self::intValue($data, 'pollServerId'),
        );
    }
}
