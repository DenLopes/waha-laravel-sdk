<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\WahaData;
use DenLopes\Waha\Enums\WahaMessageSourceEnum;

/**
 * Payload of the `message.edited` webhook event.
 *
 * This is a {@see WAMessageData} plus the ID of the original message that was
 * edited.
 */
final readonly class WAMessageEditedBodyData extends WahaData
{
    /**
     * @param  string[]|null  $vCards  List of vCards contained in the message.
     * @param  array|null  $raw  Raw message data from WhatsApp.
     */
    public function __construct(
        public string $id,
        public ?int $timestamp,
        public ?string $from,
        public ?bool $fromMe,
        public ?WahaMessageSourceEnum $source,
        public ?string $to,
        public ?string $participant,
        public ?string $body,
        public ?bool $hasMedia,
        public ?WAMediaData $media,
        public ?string $mediaUrl,
        public ?int $ack,
        public ?string $ackName,
        public ?string $author,
        public ?WALocationData $location,
        public ?ReplyToMessageData $replyTo,
        public ?array $vCards,
        public ?array $raw,
        public ?string $editedMessageId,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            timestamp: isset($data['timestamp']) ? (int) $data['timestamp'] : null,
            from: isset($data['from']) ? (string) $data['from'] : null,
            fromMe: isset($data['fromMe']) ? (bool) $data['fromMe'] : null,
            source: WahaMessageSourceEnum::tryFrom((string) ($data['source'] ?? '')),
            to: isset($data['to']) ? (string) $data['to'] : null,
            participant: isset($data['participant']) ? (string) $data['participant'] : null,
            body: isset($data['body']) ? (string) $data['body'] : null,
            hasMedia: isset($data['hasMedia']) ? (bool) $data['hasMedia'] : null,
            media: isset($data['media']) && is_array($data['media'])
                ? WAMediaData::fromArray($data['media'])
                : null,
            mediaUrl: isset($data['mediaUrl']) ? (string) $data['mediaUrl'] : null,
            ack: isset($data['ack']) ? (int) $data['ack'] : null,
            ackName: isset($data['ackName']) ? (string) $data['ackName'] : null,
            author: isset($data['author']) ? (string) $data['author'] : null,
            location: isset($data['location']) && is_array($data['location'])
                ? WALocationData::fromArray($data['location'])
                : null,
            replyTo: isset($data['replyTo']) && is_array($data['replyTo'])
                ? ReplyToMessageData::fromArray($data['replyTo'])
                : null,
            vCards: $data['vCards'] ?? null,
            raw: $data['_data'] ?? null,
            editedMessageId: isset($data['editedMessageId']) ? (string) $data['editedMessageId'] : null,
        );
    }
}
