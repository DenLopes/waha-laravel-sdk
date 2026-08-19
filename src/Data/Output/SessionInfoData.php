<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\AppData;
use DenLopes\Waha\Data\Input\SessionConfigData;
use DenLopes\Waha\Data\WahaData;
use DenLopes\Waha\Enums\WahaSessionStatusEnum;

final readonly class SessionInfoData extends WahaData
{
    /**
     * @param  AppData[]|null  $apps  Apps configured for the session.
     * @param  array|null  $presence  Raw presence object.
     * @param  array|null  $timestamps  Raw timestamps object.
     * @param  SessionConfigData|null  $config  Session config.
     */
    public function __construct(
        public string $name,
        public ?array $apps,
        public ?MeInfoData $me,
        public ?string $assignedWorker,
        public ?array $presence,
        public ?array $timestamps,
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
            apps: isset($data['apps']) && is_array($data['apps'])
                ? array_map(static fn (array $app) => AppData::fromArray($app), $data['apps'])
                : null,
            me: isset($data['me']) && is_array($data['me'])
                ? MeInfoData::fromArray($data['me'])
                : null,
            assignedWorker: self::string($data, 'assignedWorker'),
            presence: self::arrayValue($data, 'presence'),
            timestamps: self::arrayValue($data, 'timestamps'),
            status: WahaSessionStatusEnum::tryFrom((string) ($data['status'] ?? '')),
            config: isset($data['config']) && is_array($data['config'])
                ? SessionConfigData::fromArray($data['config'])
                : null,
        );
    }
}
