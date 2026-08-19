<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\WahaData;

/**
 * Payload of the `event.response` and `event.response.failed` webhook events.
 */
final readonly class EventResponsePayloadData extends WahaData
{
    /**
     * @param  array|null  $raw  Raw event response data.
     */
    public function __construct(
        public string $id,
        public int $timestamp,
        public ?string $from,
        public ?bool $fromMe,
        public ?string $source,
        public ?string $to,
        public ?string $participant,
        public ?array $raw,
        public ?MessageDestinationData $eventCreationKey,
        public ?EventResponseData $eventResponse,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            timestamp: (int) ($data['timestamp'] ?? 0),
            from: $data['from'] ?? null,
            fromMe: $data['fromMe'] ?? null,
            source: $data['source'] ?? null,
            to: $data['to'] ?? null,
            participant: $data['participant'] ?? null,
            raw: $data['_data'] ?? null,
            eventCreationKey: isset($data['eventCreationKey']) && is_array($data['eventCreationKey'])
                ? MessageDestinationData::fromArray($data['eventCreationKey'])
                : null,
            eventResponse: isset($data['eventResponse']) && is_array($data['eventResponse'])
                ? EventResponseData::fromArray($data['eventResponse'])
                : null,
        );
    }
}
