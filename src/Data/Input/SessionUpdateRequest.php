<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\App;
use DenLopes\Waha\Data\Data;

/**
 * Payload for `PUT /api/sessions/{session}`.
 */
final readonly class SessionUpdateRequest extends Data
{
    /**
     * @param  App[]|null  $apps
     */
    public function __construct(
        public ?SessionConfig $config = null,
        public ?array $apps = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            config: isset($data['config']) && is_array($data['config'])
                ? SessionConfig::fromArray($data['config'])
                : null,
            apps: isset($data['apps']) && is_array($data['apps'])
                ? array_map(static fn (array $app) => App::fromArray($app), $data['apps'])
                : null,
        );
    }
}
