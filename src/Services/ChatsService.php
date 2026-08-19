<?php

declare(strict_types=1);

namespace DenLopes\Waha\Services;

use DenLopes\Waha\Concerns\SendsWahaRequests;
use DenLopes\Waha\Data\Input\OverviewBodyRequestData;
use DenLopes\Waha\Data\Output\ChatPictureData;
use DenLopes\Waha\Data\Output\ChatData;
use DenLopes\Waha\Data\Output\ChatSummaryData;
use DenLopes\Waha\Data\Output\ReadChatMessagesData;
use DenLopes\Waha\Data\Output\WAMessageData;
use DenLopes\Waha\Enums\WahaAckEnum;
use DenLopes\Waha\Enums\WahaChatSortFieldEnum;
use DenLopes\Waha\Enums\WahaMessageSortFieldEnum;
use DenLopes\Waha\Enums\WahaSortOrderEnum;
use DenLopes\Waha\Support\WahaSession;

class ChatsService
{
    use SendsWahaRequests;

    /**
     * Get all chats for a session.
     *
     * @return ChatData[]
     */
    public function getChats(
        ?WahaSession $session = null,
        ?WahaChatSortFieldEnum $sortBy = null,
        ?WahaSortOrderEnum $sortOrder = null,
        ?bool $merge = null,
        ?int $limit = null,
        ?int $offset = null,
    ): array {
        $payload = [];

        if ($sortBy !== null) {
            $payload['sortBy'] = $sortBy->value;
        }

        if ($sortOrder !== null) {
            $payload['sortOrder'] = $sortOrder->value;
        }

        if ($merge !== null) {
            $payload['merge'] = $merge;
        }

        if ($limit !== null) {
            $payload['limit'] = $limit;
        }

        if ($offset !== null) {
            $payload['offset'] = $offset;
        }

        $data = $this->send('get', "/api/{$this->session($session)}/chats", $payload, 'Communication with WAHA failed while fetching chats.');

        return array_map(
            static fn (array $item) => ChatData::fromArray($item),
            $data,
        );
    }

    /**
     * Get a chats overview suitable for building a "chats" UI.
     *
     * @return ChatSummaryData[]
     */
    public function getChatsOverview(
        ?WahaSession $session = null,
        ?bool $merge = null,
        ?int $limit = 20,
        ?int $offset = null,
        ?array $ids = null,
    ): array {
        $payload = [];

        if ($merge !== null) {
            $payload['merge'] = $merge;
        }

        if ($limit !== null) {
            $payload['limit'] = $limit;
        }

        if ($offset !== null) {
            $payload['offset'] = $offset;
        }

        if ($ids !== null) {
            $payload['ids'] = $ids;
        }

        $data = $this->send('get', "/api/{$this->session($session)}/chats/overview", $payload, 'Communication with WAHA failed while fetching the chats overview.');

        return array_map(
            static fn (array $item) => ChatSummaryData::fromArray($item),
            $data,
        );
    }

    /**
     * Get a chat picture.
     */
    public function getChatPicture(WahaSession $session, string $chatId, bool $refresh = false): ChatPictureData
    {
        $data = $this->send('get', "/api/{$this->session($session)}/chats/{$chatId}/picture", [
            'refresh' => $refresh,
        ], 'Communication with WAHA failed while fetching the chat picture.');

        return ChatPictureData::fromArray($data);
    }

    /**
     * Get messages in a chat.
     *
     * @return WAMessageData[]
     */
    public function getChatMessages(
        WahaSession $session,
        string $chatId,
        ?int $limit = 10,
        ?int $offset = null,
        ?WahaMessageSortFieldEnum $sortBy = null,
        ?WahaSortOrderEnum $sortOrder = null,
        ?bool $downloadMedia = null,
        ?bool $merge = null,
        ?int $timestampLte = null,
        ?int $timestampGte = null,
        ?bool $fromMe = null,
        ?WahaAckEnum $ack = null,
    ): array {
        $payload = ['limit' => $limit];

        if ($offset !== null) {
            $payload['offset'] = $offset;
        }

        if ($sortBy !== null) {
            $payload['sortBy'] = $sortBy->value;
        }

        if ($sortOrder !== null) {
            $payload['sortOrder'] = $sortOrder->value;
        }

        if ($downloadMedia !== null) {
            $payload['downloadMedia'] = $downloadMedia;
        }

        if ($merge !== null) {
            $payload['merge'] = $merge;
        }

        if ($timestampLte !== null) {
            $payload['filter.timestamp.lte'] = $timestampLte;
        }

        if ($timestampGte !== null) {
            $payload['filter.timestamp.gte'] = $timestampGte;
        }

        if ($fromMe !== null) {
            $payload['filter.fromMe'] = $fromMe;
        }

        if ($ack !== null) {
            $payload['filter.ack'] = $ack->value;
        }

        $data = $this->send(
            'get',
            "/api/{$this->session($session)}/chats/{$chatId}/messages",
            $payload,
            'Communication with WAHA failed while fetching chat messages.',
        );

        return array_map(
            static fn (array $item) => WAMessageData::fromArray($item),
            $data,
        );
    }

    /**
     * Get a single message from a chat.
     */
    public function getChatMessage(
        WahaSession $session,
        string $chatId,
        string $messageId,
        ?bool $downloadMedia = true,
        ?bool $merge = true,
    ): WAMessageData {
        $payload = [];

        if ($downloadMedia !== null) {
            $payload['downloadMedia'] = $downloadMedia;
        }

        if ($merge !== null) {
            $payload['merge'] = $merge;
        }

        $data = $this->send(
            'get',
            "/api/{$this->session($session)}/chats/{$chatId}/messages/{$messageId}",
            $payload,
            'Communication with WAHA failed while fetching the chat message.',
        );

        return WAMessageData::fromArray($data);
    }

    /**
     * Edit a message in a chat.
     */
    public function editMessage(
        WahaSession $session,
        string $chatId,
        string $messageId,
        string $text,
        ?bool $linkPreview = true,
        ?bool $linkPreviewHighQuality = false,
    ): array {
        $payload = ['text' => $text];

        if ($linkPreview !== null) {
            $payload['linkPreview'] = $linkPreview;
        }

        if ($linkPreviewHighQuality !== null) {
            $payload['linkPreviewHighQuality'] = $linkPreviewHighQuality;
        }

        return $this->send(
            'put',
            "/api/{$this->session($session)}/chats/{$chatId}/messages/{$messageId}",
            $payload,
            'Communication with WAHA failed while editing the message.',
        );
    }

    /**
     * Delete a message from a chat.
     */
    public function deleteMessage(WahaSession $session, string $chatId, string $messageId): array
    {
        return $this->send('delete', "/api/{$this->session($session)}/chats/{$chatId}/messages/{$messageId}", [], 'Communication with WAHA failed while deleting the message.');
    }

    /**
     * Clear all messages from a chat.
     */
    public function clearMessages(WahaSession $session, string $chatId): array
    {
        return $this->send('delete', "/api/{$this->session($session)}/chats/{$chatId}/messages", [], 'Communication with WAHA failed while clearing the chat messages.');
    }

    /**
     * Mark unread messages in a chat as read.
     */
    public function readChatMessages(
        WahaSession $session,
        string $chatId,
        ?int $messages = null,
        ?int $days = null,
    ): ReadChatMessagesData {
        $query = [];

        if ($messages !== null) {
            $query['messages'] = $messages;
        }

        if ($days !== null) {
            $query['days'] = $days;
        }

        $data = $this->send(
            'post',
            "/api/{$this->session($session)}/chats/{$chatId}/messages/read",
            [],
            'Communication with WAHA failed while reading the chat messages.',
            $query,
        );

        return ReadChatMessagesData::fromArray($data);
    }

    /**
     * Pin a message in a chat.
     */
    public function pinMessage(WahaSession $session, string $chatId, string $messageId, int $duration = 86400): array
    {
        return $this->send('post', "/api/{$this->session($session)}/chats/{$chatId}/messages/{$messageId}/pin", [
            'duration' => $duration,
        ], 'Communication with WAHA failed while pinning the message.');
    }

    /**
     * Unpin a message in a chat.
     */
    public function unpinMessage(WahaSession $session, string $chatId, string $messageId): array
    {
        return $this->send('post', "/api/{$this->session($session)}/chats/{$chatId}/messages/{$messageId}/unpin", [], 'Communication with WAHA failed while unpinning the message.');
    }

    /**
     * Archive a chat.
     */
    public function archiveChat(WahaSession $session, string $chatId): array
    {
        return $this->send('post', "/api/{$this->session($session)}/chats/{$chatId}/archive", [], 'Communication with WAHA failed while archiving the chat.');
    }

    /**
     * Unarchive a chat.
     */
    public function unarchiveChat(WahaSession $session, string $chatId): array
    {
        return $this->send('post', "/api/{$this->session($session)}/chats/{$chatId}/unarchive", [], 'Communication with WAHA failed while unarchiving the chat.');
    }

    /**
     * Mark a chat as unread.
     */
    public function unreadChat(WahaSession $session, string $chatId): array
    {
        return $this->send('post', "/api/{$this->session($session)}/chats/{$chatId}/unread", [], 'Communication with WAHA failed while marking the chat as unread.');
    }

    /**
     * Delete a chat.
     */
    public function deleteChat(WahaSession $session, string $chatId): array
    {
        return $this->send('delete', "/api/{$this->session($session)}/chats/{$chatId}", [], 'Communication with WAHA failed while deleting the chat.');
    }

    /**
     * Get a chats overview using POST (useful when the ids filter is large).
     *
     * @return ChatSummaryData[]
     */
    public function getChatsOverviewPost(WahaSession $session, OverviewBodyRequestData $request): array
    {
        $data = $this->send('post', "/api/{$this->session($session)}/chats/overview", $request->toArray(), 'Communication with WAHA failed while fetching the chats overview.');

        return array_map(
            static fn (array $item) => ChatSummaryData::fromArray($item),
            $data,
        );
    }
}
