<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\Data;

/**
 * The message a given message was sent in reply to.
 */
final readonly class ReplyToMessage extends Data
{
    /**
     * @param  array|null  $raw  Raw reply message data.
     */
    public function __construct(
        public ?string $id,
        public ?string $participant,
        public ?string $body,
        public ?bool $hasMedia,
        public ?Media $media,
        public ?array $raw,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: isset($data['id']) ? (string) $data['id'] : null,
            participant: isset($data['participant']) ? (string) $data['participant'] : null,
            body: isset($data['body']) ? (string) $data['body'] : null,
            hasMedia: isset($data['hasMedia']) ? (bool) $data['hasMedia'] : null,
            media: isset($data['media']) && is_array($data['media'])
                ? Media::fromArray($data['media'])
                : null,
            raw: $data['_data'] ?? null,
        );
    }
}
