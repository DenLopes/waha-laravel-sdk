<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\Data;

final readonly class ChatSummary extends Data
{
    /**
     * @param  array|null  $lastMessage  Raw last message object.
     * @param  array|null  $chat  Raw chat object.
     */
    public function __construct(
        public string $id,
        public ?string $name,
        public ?string $picture,
        public ?array $lastMessage,
        public ?array $chat,
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
            name: $data['name'] ?? null,
            picture: $data['picture'] ?? null,
            lastMessage: $data['lastMessage'] ?? null,
            chat: $data['_chat'] ?? null,
        );
    }
}
