<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\SessionActionsData;
use DenLopes\Waha\Data\WahaData;

/**
 * Payload for creating or updating an API key.
 */
final readonly class ApiKeyRequestData extends WahaData
{
    public function __construct(
        public bool $isAdmin = false,
        public ?string $session = null,
        public bool $isActive = true,
        public ?SessionActionsData $actions = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            isAdmin: (bool) ($data['isAdmin'] ?? false),
            session: self::string($data, 'session'),
            isActive: (bool) ($data['isActive'] ?? true),
            actions: isset($data['actions']) && is_array($data['actions'])
                ? SessionActionsData::fromArray($data['actions'])
                : null,
        );
    }
}
