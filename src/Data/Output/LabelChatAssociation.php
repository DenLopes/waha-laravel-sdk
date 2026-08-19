<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\Data;

/**
 * Payload of the `label.chat.added` and `label.chat.deleted` webhook events.
 */
final readonly class LabelChatAssociation extends Data
{
    public function __construct(
        public string $labelId,
        public ?string $chatId,
        public ?Label $label,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            labelId: (string) ($data['labelId'] ?? ''),
            chatId: $data['chatId'] ?? null,
            label: isset($data['label']) && is_array($data['label'])
                ? Label::fromArray($data['label'])
                : null,
        );
    }
}
