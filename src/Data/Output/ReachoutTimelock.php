<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\Data;
use DenLopes\Waha\Enums\ReachoutEnforcementType;

final readonly class ReachoutTimelock extends Data
{
    public function __construct(
        public ?ReachoutEnforcementType $enforcementType,
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
            enforcementType: ReachoutEnforcementType::tryFrom($enforcementTypeRaw),
            enforcementTypeRaw: $enforcementTypeRaw,
            isActive: (bool) ($data['isActive'] ?? false),
            timeEnforcementEnds: isset($data['timeEnforcementEnds'])
                ? (int) $data['timeEnforcementEnds']
                : null,
        );
    }
}
