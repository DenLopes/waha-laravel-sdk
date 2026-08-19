<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * Payload for searching channels by text.
 */
final readonly class ChannelSearchByTextData extends WahaData
{
    /**
     * @param  string[]  $categories
     */
    public function __construct(
        public string $text,
        public array $categories = [],
        public int $limit = 50,
        public string $startCursor = '',
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            text: (string) ($data['text'] ?? ''),
            categories: (array) ($data['categories'] ?? []),
            limit: (int) ($data['limit'] ?? 50),
            startCursor: (string) ($data['startCursor'] ?? ''),
        );
    }
}
