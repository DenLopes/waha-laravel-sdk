<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * Payload for updating the profile picture.
 */
final readonly class ProfilePictureRequest extends Data
{
    public function __construct(public RemoteFile|BinaryFile $file) {}

    public static function fromArray(array $data): static
    {
        $file = $data['file'] ?? [];

        return new self(
            file: is_array($file)
                ? (isset($file['url'])
                    ? RemoteFile::fromArray($file)
                    : BinaryFile::fromArray($file))
                : new RemoteFile('', ''),
        );
    }
}
