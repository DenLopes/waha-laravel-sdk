<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;
use DenLopes\Waha\Data\SessionActions;

/**
 * Payload for creating or updating an API key.
 */
final readonly class ApiKeyRequest extends Data
{
    public function __construct(
        public bool $isAdmin = false,
        public ?string $session = null,
        public bool $isActive = true,
        public ?SessionActions $actions = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            isAdmin: (bool) ($data['isAdmin'] ?? false),
            session: self::string($data, 'session'),
            isActive: (bool) ($data['isActive'] ?? true),
            actions: isset($data['actions']) && is_array($data['actions'])
                ? SessionActions::fromArray($data['actions'])
                : null,
        );
    }
}
