<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\WahaData;
use DenLopes\Waha\Enums\WahaEngineEnum;

final readonly class WahaEnvironmentData extends WahaData
{
    public function __construct(
        public string $version,
        public ?WahaEngineEnum $engine,
        public ?string $tier,
        public ?string $browser,
        public ?string $platform,
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
            version: (string) ($data['version'] ?? ''),
            engine: WahaEngineEnum::tryFrom((string) ($data['engine'] ?? '')),
            tier: isset($data['tier']) ? (string) $data['tier'] : null,
            browser: isset($data['browser']) ? (string) $data['browser'] : null,
            platform: isset($data['platform']) ? (string) $data['platform'] : null,
            worker: isset($data['worker']) && is_array($data['worker'])
                ? WorkerInfoData::fromArray($data['worker'])
                : null,
        );
    }
}
