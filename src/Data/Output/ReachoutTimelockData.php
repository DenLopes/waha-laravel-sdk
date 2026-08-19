<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\WahaData;

final readonly class ReachoutTimelockData extends WahaData
{
    public function __construct(
        public string $enforcementType,
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
        return new self(
            enforcementType: (string) ($data['enforcementType'] ?? ''),
            isActive: (bool) ($data['isActive'] ?? false),
            timeEnforcementEnds: isset($data['timeEnforcementEnds'])
                ? (int) $data['timeEnforcementEnds']
                : null,
        );
    }
}
