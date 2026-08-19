<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * ChatWoot slash-command toggles.
 */
final readonly class ChatWootCommandsConfigData extends WahaData
{
    public function __construct(
        public bool $server = true,
        public bool $queue = false,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            server: (bool) ($data['server'] ?? true),
            queue: (bool) ($data['queue'] ?? false),
        );
    }
}
