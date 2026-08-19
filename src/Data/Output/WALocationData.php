<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\WahaData;

/**
 * Location information contained in a WhatsApp message.
 */
final readonly class WALocationData extends WahaData
{
    public function __construct(
        public ?string $latitude,
        public ?string $longitude,
        public ?bool $live,
        public ?string $name,
        public ?string $address,
        public ?string $url,
        public ?string $description,
        public ?string $thumbnail,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            latitude: isset($data['latitude']) ? (string) $data['latitude'] : null,
            longitude: isset($data['longitude']) ? (string) $data['longitude'] : null,
            live: isset($data['live']) ? (bool) $data['live'] : null,
            name: isset($data['name']) ? (string) $data['name'] : null,
            address: isset($data['address']) ? (string) $data['address'] : null,
            url: isset($data['url']) ? (string) $data['url'] : null,
            description: isset($data['description']) ? (string) $data['description'] : null,
            thumbnail: isset($data['thumbnail']) ? (string) $data['thumbnail'] : null,
        );
    }
}
