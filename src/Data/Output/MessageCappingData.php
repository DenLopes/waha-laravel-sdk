<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\WahaData;
use DenLopes\Waha\Enums\WahaMessageCappingStatusEnum;

final readonly class MessageCappingData extends WahaData
{
    public function __construct(
        public ?WahaMessageCappingStatusEnum $cappingStatus,
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
            cappingStatus: WahaMessageCappingStatusEnum::tryFrom($cappingStatusRaw),
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
