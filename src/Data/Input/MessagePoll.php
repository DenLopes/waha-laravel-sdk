<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * A poll definition.
 */
final readonly class MessagePoll extends Data
{
    /**
     * @param  string[]  $options
     */
    public function __construct(
        public string $name,
        public array $options,
        public bool $multipleAnswers = false,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            name: (string) ($data['name'] ?? ''),
            options: (array) ($data['options'] ?? []),
            multipleAnswers: (bool) ($data['multipleAnswers'] ?? false),
        );
    }
}
