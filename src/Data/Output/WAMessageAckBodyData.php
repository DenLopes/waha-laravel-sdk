<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\WahaData;

/**
 * Payload of the `message.ack` and `message.ack.group` webhook events.
 */
final readonly class WAMessageAckBodyData extends WahaData
{
    /**
     * @param  array|null  $raw  Raw ack data.
     */
    public function __construct(
        public string $id,
        public ?string $from,
        public ?string $to,
        public ?string $participant,
        public ?bool $fromMe,
        public ?int $ack,
        public ?string $ackName,
        public ?array $raw,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            from: isset($data['from']) ? (string) $data['from'] : null,
            to: isset($data['to']) ? (string) $data['to'] : null,
            participant: isset($data['participant']) ? (string) $data['participant'] : null,
            fromMe: isset($data['fromMe']) ? (bool) $data['fromMe'] : null,
            ack: isset($data['ack']) ? (int) $data['ack'] : null,
            ackName: isset($data['ackName']) ? (string) $data['ackName'] : null,
            raw: $data['_data'] ?? null,
        );
    }
}
