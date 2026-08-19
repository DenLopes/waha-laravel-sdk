<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * Payload for sending a voice status.
 */
final readonly class VoiceStatusData extends WahaData
{
    /**
     * @param  string[]|null  $contacts  Contact list to send the status to.
     */
    public function __construct(
        public VoiceRemoteFileData|VoiceBinaryFileData $file,
        public bool $convert,
        public string $backgroundColor = '#38b42f',
        public ?string $id = null,
        public ?array $contacts = null,
    ) {}

    public static function fromArray(array $data): static
    {
        $file = $data['file'] ?? [];

        return new self(
            file: is_array($file)
                ? (isset($file['url'])
                    ? VoiceRemoteFileData::fromArray($file)
                    : VoiceBinaryFileData::fromArray($file))
                : new VoiceRemoteFileData(''),
            convert: (bool) ($data['convert'] ?? true),
            backgroundColor: (string) ($data['backgroundColor'] ?? '#38b42f'),
            id: $data['id'] ?? null,
            contacts: $data['contacts'] ?? null,
        );
    }
}
