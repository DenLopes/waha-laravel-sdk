<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * Payload for searching channels by view.
 */
final readonly class ChannelSearchByView extends Data
{
    /**
     * @param  string[]  $countries
     * @param  string[]  $categories
     */
    public function __construct(
        public string $view = 'RECOMMENDED',
        public array $countries = ['US'],
        public array $categories = [],
        public int $limit = 50,
        public string $startCursor = '',
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            view: (string) ($data['view'] ?? 'RECOMMENDED'),
            countries: (array) ($data['countries'] ?? ['US']),
            categories: (array) ($data['categories'] ?? []),
            limit: (int) ($data['limit'] ?? 50),
            startCursor: (string) ($data['startCursor'] ?? ''),
        );
    }
}
