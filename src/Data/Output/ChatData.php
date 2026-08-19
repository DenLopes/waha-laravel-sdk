<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\WahaData;

/**
 * A WhatsApp chat returned by the chats endpoints.
 *
 * The OpenAPI document does not define a chat schema, so the commonly-used
 * fields are typed here and the full payload is preserved in {@see self::$raw}
 * for forward compatibility.
 */
final readonly class ChatData extends WahaData
{
    /**
     * @param  array<string, mixed>  $raw  The complete, unmodified chat payload.
     */
    public function __construct(
        public string $id,
        public ?string $name = null,
        public ?bool $isGroup = null,
        public ?int $unreadCount = null,
        public ?int $timestamp = null,
        public mixed $lastMessage = null,
        public array $raw = [],
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            name: isset($data['name']) ? (string) $data['name'] : null,
            isGroup: isset($data['isGroup']) ? (bool) $data['isGroup'] : null,
            unreadCount: isset($data['unreadCount']) ? (int) $data['unreadCount'] : null,
            timestamp: isset($data['timestamp']) ? (int) $data['timestamp'] : null,
            lastMessage: $data['lastMessage'] ?? null,
            raw: $data,
        );
    }
}
