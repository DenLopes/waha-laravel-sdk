<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\Data;
use DenLopes\Waha\Enums\Engine;

final readonly class Environment extends Data
{
    public function __construct(
        public string $version,
        public ?Engine $engine,
        public ?string $tier,
        public ?string $browser,
        public ?string $platform,
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
            version: (string) ($data['version'] ?? ''),
            engine: Engine::tryFrom((string) ($data['engine'] ?? '')),
            tier: isset($data['tier']) ? (string) $data['tier'] : null,
            browser: isset($data['browser']) ? (string) $data['browser'] : null,
            platform: isset($data['platform']) ? (string) $data['platform'] : null,
            worker: isset($data['worker']) && is_array($data['worker'])
                ? WorkerInfo::fromArray($data['worker'])
                : null,
        );
    }
}
