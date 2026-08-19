<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\WahaData;

/**
 * Payload of the `engine.event` webhook event.
 */
final readonly class EnginePayloadData extends WahaData
{
    /**
     * @param  array|null  $data  Engine event data.
     */
    public function __construct(
        public string $event,
        public ?array $data,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            event: (string) ($data['event'] ?? ''),
            data: $data['data'] ?? null,
        );
    }
}
