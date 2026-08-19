<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * GOWS engine local storage settings.
 */
final readonly class GowsStorageConfig extends Data
{
    public function __construct(
        public ?bool $messages = null,
        public ?bool $groups = null,
        public ?bool $chats = null,
        public ?bool $labels = null,
        public ?bool $contacts = null,
        public ?bool $messageSecrets = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            messages: $data['messages'] ?? null,
            groups: $data['groups'] ?? null,
            chats: $data['chats'] ?? null,
            labels: $data['labels'] ?? null,
            contacts: $data['contacts'] ?? null,
            messageSecrets: $data['messageSecrets'] ?? null,
        );
    }
}
