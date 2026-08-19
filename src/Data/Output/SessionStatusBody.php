<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\Data;
use DenLopes\Waha\Enums\SessionStatus;

/**
 * Payload of the `session.status` webhook event.
 */
final readonly class SessionStatusBody extends Data
{
    /**
     * @param  array|null  $data  Extra info for the current status (passkey challenge, confirmation code, etc.).
     * @param  SessionStatusPoint[]  $statuses  Status history.
     */
    public function __construct(
        public string $name,
        public ?array $data,
        public ?SessionStatus $status,
        public array $statuses,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            name: (string) ($data['name'] ?? ''),
            data: $data['data'] ?? null,
            status: SessionStatus::tryFrom((string) ($data['status'] ?? '')),
            statuses: array_map(
                static fn (array $point) => SessionStatusPoint::fromArray($point),
                $data['statuses'] ?? [],
            ),
        );
    }
}
