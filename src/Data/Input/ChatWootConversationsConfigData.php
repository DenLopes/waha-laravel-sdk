<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;
use DenLopes\Waha\Enums\WahaChatWootConversationStatusEnum;
use DenLopes\Waha\Enums\WahaChatWootSortEnum;

/**
 * ChatWoot conversation listing/filter settings.
 */
final readonly class ChatWootConversationsConfigData extends WahaData
{
    /**
     * @param  array<int, WahaChatWootConversationStatusEnum|null>|null  $status
     */
    public function __construct(
        public bool $markAsRead = true,
        public WahaChatWootSortEnum $sort = WahaChatWootSortEnum::ACTIVITY_NEWEST,
        public ?array $status = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            markAsRead: (bool) ($data['markAsRead'] ?? true),
            sort: WahaChatWootSortEnum::tryFrom((string) ($data['sort'] ?? '')) ?? WahaChatWootSortEnum::ACTIVITY_NEWEST,
            status: isset($data['status']) && is_array($data['status'])
                ? array_map(
                    static fn (mixed $value): ?WahaChatWootConversationStatusEnum => WahaChatWootConversationStatusEnum::tryFrom((string) $value),
                    $data['status'],
                )
                : null,
        );
    }
}
