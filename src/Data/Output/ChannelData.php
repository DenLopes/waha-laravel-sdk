<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\WahaData;
use DenLopes\Waha\Enums\WahaChannelRoleEnum;

final readonly class ChannelData extends WahaData
{
    public function __construct(
        public string $id,
        public string $name,
        public string $invite,
        public ?string $preview,
        public ?string $picture,
        public ?WahaChannelRoleEnum $role,
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
            preview: isset($data['preview']) ? (string) $data['preview'] : null,
            picture: isset($data['picture']) ? (string) $data['picture'] : null,
            role: WahaChannelRoleEnum::tryFrom((string) ($data['role'] ?? '')),
            description: isset($data['description']) ? (string) $data['description'] : null,
            verified: (bool) ($data['verified'] ?? false),
            subscribersCount: (int) ($data['subscribersCount'] ?? 0),
        );
    }
}
