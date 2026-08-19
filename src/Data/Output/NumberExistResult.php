<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\Data;

final readonly class NumberExistResult extends Data
{
    public function __construct(
        public ?string $chatId,
        public ?string $pn,
        public bool $numberExists,
    ) {}

    /**
     * Create an instance from a raw WAHA API response array.
     *
     * @param  array  $data  Raw WAHA API response array.
     */
    public static function fromArray(array $data): static
    {
        return new self(
            chatId: isset($data['chatId']) ? (string) $data['chatId'] : null,
            pn: isset($data['pn']) ? (string) $data['pn'] : null,
            numberExists: (bool) ($data['numberExists'] ?? false),
        );
    }
}
