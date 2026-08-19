<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * Payload for creating a channel.
 */
final readonly class CreateChannelRequestData extends WahaData
{
    public function __construct(
        public string $name,
        public ?string $description = null,
        public RemoteFileData|BinaryFileData|null $picture = null,
    ) {}

    public static function fromArray(array $data): static
    {
        $picture = $data['picture'] ?? null;

        return new self(
            name: (string) ($data['name'] ?? ''),
            description: $data['description'] ?? null,
            picture: is_array($picture)
                ? (isset($picture['url'])
                    ? RemoteFileData::fromArray($picture)
                    : BinaryFileData::fromArray($picture))
                : null,
        );
    }
}
