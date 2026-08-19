<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\Data;
use DenLopes\Waha\Enums\EventResponseStatus;

/**
 * An RSVP response to an event message.
 */
final readonly class EventResponse extends Data
{
    public function __construct(
        public ?EventResponseStatus $response,
        public int $timestampMs,
        public int $extraGuestCount,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            response: EventResponseStatus::tryFrom((string) ($data['response'] ?? '')),
            timestampMs: (int) ($data['timestampMs'] ?? 0),
            extraGuestCount: (int) ($data['extraGuestCount'] ?? 0),
        );
    }
}
