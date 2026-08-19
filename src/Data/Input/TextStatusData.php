<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * Payload for sending a text status.
 */
final readonly class TextStatusData extends WahaData
{
    /**
     * @param  string[]|null  $contacts  Contact list to send the status to.
     */
    public function __construct(
        public string $text,
        public string $backgroundColor = '#38b42f',
        public int $font = 0,
        public ?string $id = null,
        public ?array $contacts = null,
        public ?bool $linkPreview = null,
        public ?bool $linkPreviewHighQuality = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            text: (string) ($data['text'] ?? ''),
            backgroundColor: (string) ($data['backgroundColor'] ?? '#38b42f'),
            font: (int) ($data['font'] ?? 0),
            id: $data['id'] ?? null,
            contacts: $data['contacts'] ?? null,
            linkPreview: $data['linkPreview'] ?? null,
            linkPreviewHighQuality: $data['linkPreviewHighQuality'] ?? null,
        );
    }
}
