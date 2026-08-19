<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\SessionActionsData;
use DenLopes\Waha\Data\WahaData;

final readonly class ApiKeyData extends WahaData
{
    public function __construct(
        public string $id,
        public string $key,
        public bool $isActive,
        public bool $isAdmin,
        public ?string $session,
        public ?SessionActionsData $actions,
    ) {}

    /**
     * Create an instance from a raw WAHA API response array.
     *
     * @param  array  $data  Raw WAHA API response array.
     */
    public static function fromArray(array $data): static
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            key: (string) ($data['key'] ?? ''),
            isActive: (bool) ($data['isActive'] ?? false),
            isAdmin: (bool) ($data['isAdmin'] ?? false),
            session: $data['session'] ?? null,
            actions: isset($data['actions']) && is_array($data['actions'])
                ? SessionActionsData::fromArray($data['actions'])
                : null,
        );
    }
}
