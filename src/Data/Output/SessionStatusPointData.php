<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\WahaData;
use DenLopes\Waha\Enums\WahaSessionStatusEnum;

/**
 * A single point in a session's status history.
 */
final readonly class SessionStatusPointData extends WahaData
{
    public function __construct(
        public ?WahaSessionStatusEnum $status,
        public int $timestamp,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            status: WahaSessionStatusEnum::tryFrom((string) ($data['status'] ?? '')),
            timestamp: (int) ($data['timestamp'] ?? 0),
        );
    }
}
