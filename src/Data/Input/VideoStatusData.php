<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * Payload for sending a video status.
 */
final readonly class VideoStatusData extends WahaData
{
    /**
     * @param  string[]|null  $contacts  Contact list to send the status to.
     */
    public function __construct(
        public VideoRemoteFileData|VideoBinaryFileData $file,
        public bool $convert,
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
                    ? VideoRemoteFileData::fromArray($file)
                    : VideoBinaryFileData::fromArray($file))
                : new VideoRemoteFileData(''),
            convert: (bool) ($data['convert'] ?? true),
            id: $data['id'] ?? null,
            contacts: $data['contacts'] ?? null,
            caption: $data['caption'] ?? null,
        );
    }
}
