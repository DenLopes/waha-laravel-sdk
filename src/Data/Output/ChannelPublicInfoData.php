<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\WahaData;

final readonly class ChannelPublicInfoData extends WahaData
{
    public function __construct(
        public string $id,
        public string $name,
        public string $invite,
        public ?string $preview,
        public ?string $picture,
        public ?string $description,
        public bool $verified,
        public int $subscribersCount,
    ) {}

    /**
     * Create an instance from a raw WAHA API response array.
     *
     * @param  array  $data  Raw WAHA API response array.
     */
    public static function fromArray(array $data): static
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            name: (string) ($data['name'] ?? ''),
            invite: (string) ($data['invite'] ?? ''),
            preview: $data['preview'] ?? null,
            picture: $data['picture'] ?? null,
            description: $data['description'] ?? null,
            verified: (bool) ($data['verified'] ?? false),
            subscribersCount: (int) ($data['subscribersCount'] ?? 0),
        );
    }
}
