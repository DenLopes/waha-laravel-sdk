<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * Payload for sending an image status.
 */
final readonly class ImageStatus extends Data
{
    /**
     * @param  string[]|null  $contacts  Contact list to send the status to.
     */
    public function __construct(
        public RemoteFile|BinaryFile $file,
        public ?string $id = null,
        public ?array $contacts = null,
        public ?string $caption = null,
    ) {}

    public static function fromArray(array $data): static
    {
        $file = $data['file'] ?? [];

        return new self(
            file: is_array($file)
                ? (isset($file['url'])
                    ? RemoteFile::fromArray($file)
                    : BinaryFile::fromArray($file))
                : new RemoteFile('', ''),
            id: $data['id'] ?? null,
            contacts: $data['contacts'] ?? null,
            caption: $data['caption'] ?? null,
        );
    }
}
