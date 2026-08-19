<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\App;
use DenLopes\Waha\Data\Data;

/**
 * Payload for `POST /api/sessions`.
 */
final readonly class SessionCreateRequest extends Data
{
    /**
     * @param  App[]|null  $apps
     */
    public function __construct(
        public string $name,
        public bool $start = true,
        public ?SessionConfig $config = null,
        public ?array $apps = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            name: (string) ($data['name'] ?? ''),
            start: (bool) ($data['start'] ?? true),
            config: isset($data['config']) && is_array($data['config'])
                ? SessionConfig::fromArray($data['config'])
                : null,
            apps: isset($data['apps']) && is_array($data['apps'])
                ? array_map(static fn (array $app) => App::fromArray($app), $data['apps'])
                : null,
        );
    }
}
