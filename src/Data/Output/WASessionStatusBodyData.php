<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\WahaData;
use DenLopes\Waha\Enums\WahaSessionStatusEnum;

/**
 * Payload of the `session.status` webhook event.
 */
final readonly class WASessionStatusBodyData extends WahaData
{
    /**
     * @param  array|null  $data  Extra info for the current status (passkey challenge, confirmation code, etc.).
     * @param  SessionStatusPointData[]  $statuses  Status history.
     */
    public function __construct(
        public string $name,
        public ?array $data,
        public ?WahaSessionStatusEnum $status,
        public array $statuses,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            name: (string) ($data['name'] ?? ''),
            data: $data['data'] ?? null,
            status: WahaSessionStatusEnum::tryFrom((string) ($data['status'] ?? '')),
            statuses: array_map(
                static fn (array $point) => SessionStatusPointData::fromArray($point),
                $data['statuses'] ?? [],
            ),
        );
    }
}
