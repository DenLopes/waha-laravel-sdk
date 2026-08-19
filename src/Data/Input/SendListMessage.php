<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * Body of an interactive list message.
 */
final readonly class SendListMessage extends Data
{
    /**
     * @param  Section[]  $sections
     */
    public function __construct(
        public string $title,
        public string $button,
        public array $sections,
        public ?string $description = null,
        public ?string $footer = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            title: (string) ($data['title'] ?? ''),
            button: (string) ($data['button'] ?? ''),
            sections: array_map(
                static fn (array $section) => Section::fromArray($section),
                $data['sections'] ?? [],
            ),
            description: $data['description'] ?? null,
            footer: $data['footer'] ?? null,
        );
    }
}
