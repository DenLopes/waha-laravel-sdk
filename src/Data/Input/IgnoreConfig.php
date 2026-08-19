<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * Config for ignoring certain session event types.
 */
final readonly class IgnoreConfig extends Data
{
    public function __construct(
        public ?bool $status = null,
        public ?bool $groups = null,
        public ?bool $channels = null,
        public ?bool $broadcast = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            status: $data['status'] ?? null,
            groups: $data['groups'] ?? null,
            channels: $data['channels'] ?? null,
            broadcast: $data['broadcast'] ?? null,
        );
    }
}
