<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * Payload for updating the profile picture.
 */
final readonly class ProfilePictureRequestData extends WahaData
{
    public function __construct(public RemoteFileData|BinaryFileData $file) {}

    public static function fromArray(array $data): static
    {
        $file = $data['file'] ?? [];

        return new self(
            file: is_array($file)
                ? (isset($file['url'])
                    ? RemoteFileData::fromArray($file)
                    : BinaryFileData::fromArray($file))
                : new RemoteFileData('', ''),
        );
    }
}
