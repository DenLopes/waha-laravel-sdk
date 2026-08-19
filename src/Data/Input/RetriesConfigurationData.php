<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;
use DenLopes\Waha\Enums\WahaRetriesPolicyEnum;

/**
 * Webhook delivery retry policy.
 */
final readonly class RetriesConfigurationData extends WahaData
{
    public function __construct(
        public int $delaySeconds,
        public int $attempts,
        public WahaRetriesPolicyEnum $policy = WahaRetriesPolicyEnum::LINEAR,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            delaySeconds: (int) ($data['delaySeconds'] ?? 0),
            attempts: (int) ($data['attempts'] ?? 0),
            policy: WahaRetriesPolicyEnum::tryFrom((string) ($data['policy'] ?? ''))
                ?? WahaRetriesPolicyEnum::LINEAR,
        );
    }
}
