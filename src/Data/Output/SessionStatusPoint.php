<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\Data;
use DenLopes\Waha\Enums\SessionStatus;

/**
 * A single point in a session's status history.
 */
final readonly class SessionStatusPoint extends Data
{
    public function __construct(
        public ?SessionStatus $status,
        public int $timestamp,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            status: SessionStatus::tryFrom((string) ($data['status'] ?? '')),
            timestamp: (int) ($data['timestamp'] ?? 0),
        );
    }
}
