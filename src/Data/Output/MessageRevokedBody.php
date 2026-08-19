<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\Data;

/**
 * Payload of the `message.revoked` webhook event.
 */
final readonly class MessageRevokedBody extends Data
{
    /**
     * @param  array|null  $raw  Raw revoked data.
     */
    public function __construct(
        public ?string $revokedMessageId,
        public ?MessageData $after,
        public ?MessageData $before,
        public ?array $raw,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            revokedMessageId: $data['revokedMessageId'] ?? null,
            after: isset($data['after']) && is_array($data['after'])
                ? MessageData::fromArray($data['after'])
                : null,
            before: isset($data['before']) && is_array($data['before'])
                ? MessageData::fromArray($data['before'])
                : null,
            raw: $data['_data'] ?? null,
        );
    }
}
