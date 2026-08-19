<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\AppData;
use DenLopes\Waha\Data\WahaData;

/**
 * Payload for `PUT /api/sessions/{session}`.
 */
final readonly class SessionUpdateRequestData extends WahaData
{
    /**
     * @param  AppData[]|null  $apps
     */
    public function __construct(
        public ?SessionConfigData $config = null,
        public ?array $apps = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            config: isset($data['config']) && is_array($data['config'])
                ? SessionConfigData::fromArray($data['config'])
                : null,
            apps: isset($data['apps']) && is_array($data['apps'])
                ? array_map(static fn (array $app) => AppData::fromArray($app), $data['apps'])
                : null,
        );
    }
}
