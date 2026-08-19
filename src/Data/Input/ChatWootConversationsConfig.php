<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;
use DenLopes\Waha\Enums\ChatWootConversationStatus;
use DenLopes\Waha\Enums\ChatWootSort;

/**
 * ChatWoot conversation listing/filter settings.
 */
final readonly class ChatWootConversationsConfig extends Data
{
    /**
     * @param  array<int, ChatWootConversationStatus|null>|null  $status
     */
    public function __construct(
        public bool $markAsRead = true,
        public ChatWootSort $sort = ChatWootSort::ACTIVITY_NEWEST,
        public ?array $status = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            markAsRead: (bool) ($data['markAsRead'] ?? true),
            sort: ChatWootSort::tryFrom((string) ($data['sort'] ?? '')) ?? ChatWootSort::ACTIVITY_NEWEST,
            status: isset($data['status']) && is_array($data['status'])
                ? array_map(
                    static fn (mixed $value): ?ChatWootConversationStatus => ChatWootConversationStatus::tryFrom((string) $value),
                    $data['status'],
                )
                : null,
        );
    }
}
