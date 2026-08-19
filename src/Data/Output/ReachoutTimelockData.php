<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\WahaData;
use DenLopes\Waha\Enums\WahaReachoutEnforcementTypeEnum;

final readonly class ReachoutTimelockData extends WahaData
{
    public function __construct(
        public ?WahaReachoutEnforcementTypeEnum $enforcementType,
        public string $enforcementTypeRaw,
        public bool $isActive,
        public ?int $timeEnforcementEnds,
    ) {}

    /**
     * Create an instance from a raw WAHA API response array.
     *
     * @param  array  $data  Raw WAHA API response array.
     */
    public static function fromArray(array $data): static
    {
        $enforcementTypeRaw = (string) ($data['enforcementType'] ?? '');

        return new self(
            enforcementType: WahaReachoutEnforcementTypeEnum::tryFrom($enforcementTypeRaw),
            enforcementTypeRaw: $enforcementTypeRaw,
            isActive: (bool) ($data['isActive'] ?? false),
            timeEnforcementEnds: isset($data['timeEnforcementEnds'])
                ? (int) $data['timeEnforcementEnds']
                : null,
        );
    }
}
