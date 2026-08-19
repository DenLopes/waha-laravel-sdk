<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\WahaData;

/**
 * Payload of the `poll.vote` and `poll.vote.failed` webhook events.
 */
final readonly class PollVotePayloadData extends WahaData
{
    /**
     * @param  array|null  $raw  Raw event data.
     */
    public function __construct(
        public ?PollVoteData $vote,
        public ?MessageDestinationData $poll,
        public ?array $raw,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            vote: isset($data['vote']) && is_array($data['vote'])
                ? PollVoteData::fromArray($data['vote'])
                : null,
            poll: isset($data['poll']) && is_array($data['poll'])
                ? MessageDestinationData::fromArray($data['poll'])
                : null,
            raw: $data['_data'] ?? null,
        );
    }
}
