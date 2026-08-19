<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\Data;
use DenLopes\Waha\Data\Input\SessionConfig;
use DenLopes\Waha\Enums\SessionStatus;

final readonly class SessionData extends Data
{
    /**
     * @param  SessionConfig|null  $config  Session config.
     */
    public function __construct(
        public string $name,
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
            status: SessionStatus::tryFrom((string) ($data['status'] ?? '')),
            config: isset($data['config']) && is_array($data['config'])
                ? SessionConfig::fromArray($data['config'])
                : null,
        );
    }
}
