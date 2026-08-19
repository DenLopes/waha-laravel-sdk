<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\App;
use DenLopes\Waha\Data\Data;
use DenLopes\Waha\Data\Input\SessionConfig;
use DenLopes\Waha\Enums\SessionStatus;

final readonly class SessionInfo extends Data
{
    /**
     * @param  App[]|null  $apps  Apps configured for the session.
     * @param  array|null  $presence  Raw presence object.
     * @param  array|null  $timestamps  Raw timestamps object.
     * @param  SessionConfig|null  $config  Session config.
     */
    public function __construct(
        public string $name,
        public ?array $apps,
        public ?MeInfo $me,
        public ?string $assignedWorker,
        public ?array $presence,
        public ?array $timestamps,
        public ?SessionStatus $status,
        public ?SessionConfig $config,
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
                ? array_map(static fn (array $app) => App::fromArray($app), $data['apps'])
                : null,
            me: isset($data['me']) && is_array($data['me'])
                ? MeInfo::fromArray($data['me'])
                : null,
            assignedWorker: self::string($data, 'assignedWorker'),
            presence: self::arrayValue($data, 'presence'),
            timestamps: self::arrayValue($data, 'timestamps'),
            status: SessionStatus::tryFrom((string) ($data['status'] ?? '')),
            config: isset($data['config']) && is_array($data['config'])
                ? SessionConfig::fromArray($data['config'])
                : null,
        );
    }
}
