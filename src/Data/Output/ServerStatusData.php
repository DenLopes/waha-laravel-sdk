<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\WahaData;

final readonly class ServerStatusData extends WahaData
{
    public function __construct(
        public int $startTimestamp,
        public int $uptime,
        public ?WorkerInfoData $worker,
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
                ? WorkerInfoData::fromArray($data['worker'])
                : null,
        );
    }
}
