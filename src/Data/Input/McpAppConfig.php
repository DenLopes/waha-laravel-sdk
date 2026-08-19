<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;
use DenLopes\Waha\Data\SessionActions;

/**
 * Configuration for the built-in MCP app.
 */
final readonly class McpAppConfig extends Data
{
    public function __construct(
        public SessionActions $actions,
        public ?string $key_id = null,
        public ?string $key = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            actions: isset($data['actions']) && is_array($data['actions'])
                ? SessionActions::fromArray($data['actions'])
                : new SessionActions(
                    read: false,
                    send: false,
                    control: false,
                    setting: false,
                    app: false,
                    delete: false,
                ),
            key_id: self::string($data, 'key_id'),
            key: self::string($data, 'key'),
        );
    }
}
