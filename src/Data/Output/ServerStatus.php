<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\Data;

final readonly class ServerStatus extends Data
{
    public function __construct(
        public int $startTimestamp,
        public int $uptime,
        public ?WorkerInfo $worker,
    ) {}

    /**
     * Create an instance from a raw WAHA API response array.
     *
     * @param  array  $data  Raw WAHA API response array.
     */
    public static function fromArray(array $data): static
    {
        return new self(
            startTimestamp: (int) ($data['startTimestamp'] ?? 0),
            uptime: (int) ($data['uptime'] ?? 0),
            worker: isset($data['worker']) && is_array($data['worker'])
                ? WorkerInfo::fromArray($data['worker'])
                : null,
        );
    }
}
