<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\WahaData;
use DenLopes\Waha\Enums\WahaEventResponseEnum;

/**
 * An RSVP response to an event message.
 */
final readonly class EventResponseData extends WahaData
{
    public function __construct(
        public ?WahaEventResponseEnum $response,
        public int $timestampMs,
        public int $extraGuestCount,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            response: WahaEventResponseEnum::tryFrom((string) ($data['response'] ?? '')),
            timestampMs: (int) ($data['timestampMs'] ?? 0),
            extraGuestCount: (int) ($data['extraGuestCount'] ?? 0),
        );
    }
}
