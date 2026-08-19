<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;
use DenLopes\Waha\Enums\RetriesPolicy;

/**
 * Webhook delivery retry policy.
 */
final readonly class RetriesConfiguration extends Data
{
    public function __construct(
        public int $delaySeconds,
        public int $attempts,
        public RetriesPolicy $policy = RetriesPolicy::LINEAR,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            delaySeconds: (int) ($data['delaySeconds'] ?? 0),
            attempts: (int) ($data['attempts'] ?? 0),
            policy: RetriesPolicy::tryFrom((string) ($data['policy'] ?? ''))
                ?? RetriesPolicy::LINEAR,
        );
    }
}
