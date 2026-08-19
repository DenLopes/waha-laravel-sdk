<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\WahaData;

/**
 * A reaction (emoji) attached to a message.
 */
final readonly class WAReactionData extends WahaData
{
    public function __construct(
        public string $text,
        public string $messageId,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            text: (string) ($data['text'] ?? ''),
            messageId: (string) ($data['messageId'] ?? ''),
        );
    }
}
