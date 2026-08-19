<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\WahaData;

/**
 * S3 storage metadata attached to a media message.
 */
final readonly class S3MediaData extends WahaData
{
    public function __construct(
        public string $bucket,
        public string $key,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            bucket: (string) ($data['Bucket'] ?? ''),
            key: (string) ($data['Key'] ?? ''),
        );
    }
}
