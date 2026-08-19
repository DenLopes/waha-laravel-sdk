<?php

declare(strict_types=1);

namespace DenLopes\Waha\Services;

use DenLopes\Waha\Concerns\SendsRequests;
use DenLopes\Waha\Data\Input\OverviewBodyRequest;
use DenLopes\Waha\Data\Output\ChatData;
use DenLopes\Waha\Data\Output\ChatPicture;
use DenLopes\Waha\Data\Output\ChatSummary;
use DenLopes\Waha\Data\Output\MessageData;
use DenLopes\Waha\Data\Output\ReadChatMessages;
use DenLopes\Waha\Enums\Ack;
use DenLopes\Waha\Enums\ChatSortField;
use DenLopes\Waha\Enums\MessageSortField;
use DenLopes\Waha\Enums\SortOrder;
use DenLopes\Waha\Session;

class ChatsService
{
    use SendsRequests;

    /**
     * Get all chats for a session.
     *
     * @return ChatData[]
     */
    public function getChats(
        ?Session $session = null,
        ?ChatSortField $sortBy = null,
        ?SortOrder $sortOrder = null,
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

        $data = $this->send('get', '/api/{session}/chats', $payload, 'Communication with WAHA failed while fetching chats.', session: $session);

        return array_map(
            static fn (array $item) => ChatData::fromArray($item),
            $data,
        );
    }

    /**
     * Get a chats overview suitable for building a "chats" UI.
     *
     * @return ChatSummary[]
     */
    public function getChatsOverview(
        ?Session $session = null,
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

        $data = $this->send('get', '/api/{session}/chats/overview', $payload, 'Communication with WAHA failed while fetching the chats overview.', session: $session);

        return array_map(
            static fn (array $item) => ChatSummary::fromArray($item),
            $data,
        );
    }

    /**
     * Get a chat picture.
     */
    public function getChatPicture(Session $session, string $chatId, bool $refresh = false): ChatPicture
    {
        $data = $this->send('get', "/api/{session}/chats/{$chatId}/picture", [
            'refresh' => $refresh,
        ], 'Communication with WAHA failed while fetching the chat picture.', session: $session);

        return ChatPicture::fromArray($data);
    }

    /**
     * Get messages in a chat.
     *
     * @return MessageData[]
     */
    public function getChatMessages(
        Session $session,
        string $chatId,
        ?int $limit = 10,
        ?int $offset = null,
        ?MessageSortField $sortBy = null,
        ?SortOrder $sortOrder = null,
        ?bool $downloadMedia = null,
        ?bool $merge = null,
        ?int $timestampLte = null,
        ?int $timestampGte = null,
        ?bool $fromMe = null,
        ?Ack $ack = null,
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
            "/api/{session}/chats/{$chatId}/messages",
            $payload,
            'Communication with WAHA failed while fetching chat messages.',
            session: $session,
        );

        return array_map(
            static fn (array $item) => MessageData::fromArray($item),
            $data,
        );
    }

    /**
     * Get a single message from a chat.
     */
    public function getChatMessage(
        Session $session,
        string $chatId,
        string $messageId,
        ?bool $downloadMedia = true,
        ?bool $merge = true,
    ): MessageData {
        $payload = [];

        if ($downloadMedia !== null) {
            $payload['downloadMedia'] = $downloadMedia;
        }

        if ($merge !== null) {
            $payload['merge'] = $merge;
        }

        $data = $this->send(
            'get',
            "/api/{session}/chats/{$chatId}/messages/{$messageId}",
            $payload,
            'Communication with WAHA failed while fetching the chat message.',
            session: $session,
        );

        return MessageData::fromArray($data);
    }

    /**
     * Edit a message in a chat.
     */
    public function editMessage(
        Session $session,
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
            "/api/{session}/chats/{$chatId}/messages/{$messageId}",
            $payload,
            'Communication with WAHA failed while editing the message.',
            session: $session,
        );
    }

    /**
     * Delete a message from a chat.
     */
    public function deleteMessage(Session $session, string $chatId, string $messageId): array
    {
        return $this->send('delete', "/api/{session}/chats/{$chatId}/messages/{$messageId}", [], 'Communication with WAHA failed while deleting the message.', session: $session);
    }

    /**
     * Clear all messages from a chat.
     */
    public function clearMessages(Session $session, string $chatId): array
    {
        return $this->send('delete', "/api/{session}/chats/{$chatId}/messages", [], 'Communication with WAHA failed while clearing the chat messages.', session: $session);
    }

    /**
     * Mark unread messages in a chat as read.
     */
    public function readChatMessages(
        Session $session,
        string $chatId,
        ?int $messages = null,
        ?int $days = null,
    ): ReadChatMessages {
        $query = [];

        if ($messages !== null) {
            $query['messages'] = $messages;
        }

        if ($days !== null) {
            $query['days'] = $days;
        }

        $data = $this->send(
            'post',
            "/api/{session}/chats/{$chatId}/messages/read",
            [],
            'Communication with WAHA failed while reading the chat messages.',
            $query,
            session: $session,
        );

        return ReadChatMessages::fromArray($data);
    }

    /**
     * Pin a message in a chat.
     */
    public function pinMessage(Session $session, string $chatId, string $messageId, int $duration = 86400): array
    {
        return $this->send('post', "/api/{session}/chats/{$chatId}/messages/{$messageId}/pin", [
            'duration' => $duration,
        ], 'Communication with WAHA failed while pinning the message.', session: $session);
    }

    /**
     * Unpin a message in a chat.
     */
    public function unpinMessage(Session $session, string $chatId, string $messageId): array
    {
        return $this->send('post', "/api/{session}/chats/{$chatId}/messages/{$messageId}/unpin", [], 'Communication with WAHA failed while unpinning the message.', session: $session);
    }

    /**
     * Archive a chat.
     */
    public function archiveChat(Session $session, string $chatId): array
    {
        return $this->send('post', "/api/{session}/chats/{$chatId}/archive", [], 'Communication with WAHA failed while archiving the chat.', session: $session);
    }

    /**
     * Unarchive a chat.
     */
    public function unarchiveChat(Session $session, string $chatId): array
    {
        return $this->send('post', "/api/{session}/chats/{$chatId}/unarchive", [], 'Communication with WAHA failed while unarchiving the chat.', session: $session);
    }

    /**
     * Mark a chat as unread.
     */
    public function unreadChat(Session $session, string $chatId): array
    {
        return $this->send('post', "/api/{session}/chats/{$chatId}/unread", [], 'Communication with WAHA failed while marking the chat as unread.', session: $session);
    }

    /**
     * Delete a chat.
     */
    public function deleteChat(Session $session, string $chatId): array
    {
        return $this->send('delete', "/api/{session}/chats/{$chatId}", [], 'Communication with WAHA failed while deleting the chat.', session: $session);
    }

    /**
     * Get a chats overview using POST (useful when the ids filter is large).
     *
     * @return ChatSummary[]
     */
    public function getChatsOverviewPost(Session $session, OverviewBodyRequest $request): array
    {
        $data = $this->send('post', '/api/{session}/chats/overview', $request->toArray(), 'Communication with WAHA failed while fetching the chats overview.', session: $session);

        return array_map(
            static fn (array $item) => ChatSummary::fromArray($item),
            $data,
        );
    }
}
