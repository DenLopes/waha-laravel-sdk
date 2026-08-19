<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\Input\SessionConfigData;
use DenLopes\Waha\Data\WahaData;
use DenLopes\Waha\Enums\WahaSessionStatusEnum;

final readonly class SessionData extends WahaData
{
    /**
     * @param  SessionConfigData|null  $config  Session config.
     */
    public function __construct(
        public string $name,
        public ?WahaSessionStatusEnum $status,
        public ?SessionConfigData $config,
    ) {}

    /**
     * Create an instance from a raw WAHA API response array.
     *
     * @param  array  $data  Raw WAHA API response array.
     */
    public static function fromArray(array $data): static
    {
        return new self(
            name: (string) ($data['name'] ?? ''),
            status: WahaSessionStatusEnum::tryFrom((string) ($data['status'] ?? '')),
            config: isset($data['config']) && is_array($data['config'])
                ? SessionConfigData::fromArray($data['config'])
                : null,
        );
    }
}
