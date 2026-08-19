<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\WahaData;

/**
 * Payload of the `message.revoked` webhook event.
 */
final readonly class WAMessageRevokedBodyData extends WahaData
{
    /**
     * @param  array|null  $raw  Raw revoked data.
     */
    public function __construct(
        public ?string $revokedMessageId,
        public ?WAMessageData $after,
        public ?WAMessageData $before,
        public ?array $raw,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            revokedMessageId: $data['revokedMessageId'] ?? null,
            after: isset($data['after']) && is_array($data['after'])
                ? WAMessageData::fromArray($data['after'])
                : null,
            before: isset($data['before']) && is_array($data['before'])
                ? WAMessageData::fromArray($data['before'])
                : null,
            raw: $data['_data'] ?? null,
        );
    }
}
