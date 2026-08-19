<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\Data;
use DenLopes\Waha\Enums\MessageCappingStatus;

final readonly class MessageCapping extends Data
{
    public function __construct(
        public ?MessageCappingStatus $cappingStatus,
        public string $cappingStatusRaw,
        public int $totalQuota,
        public int $usedQuota,
        public ?int $cycleStart,
        public ?int $cycleEnd,
        public ?string $mvStatus,
        public ?string $oteStatus,
    ) {}

    public static function fromArray(array $data): static
    {
        $cappingStatusRaw = (string) ($data['cappingStatus'] ?? '');

        return new self(
            cappingStatus: MessageCappingStatus::tryFrom($cappingStatusRaw),
            cappingStatusRaw: $cappingStatusRaw,
            totalQuota: (int) ($data['totalQuota'] ?? 0),
            usedQuota: (int) ($data['usedQuota'] ?? 0),
            cycleStart: isset($data['cycleStart']) ? (int) $data['cycleStart'] : null,
            cycleEnd: isset($data['cycleEnd']) ? (int) $data['cycleEnd'] : null,
            mvStatus: isset($data['mvStatus']) ? (string) $data['mvStatus'] : null,
            oteStatus: isset($data['oteStatus']) ? (string) $data['oteStatus'] : null,
        );
    }
}
