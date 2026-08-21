<?php

declare(strict_types=1);

namespace DenLopes\Waha\Resources;

use DenLopes\Waha\Contracts\Chat as ChatContract;
use DenLopes\Waha\Data\Input\BinaryFile;
use DenLopes\Waha\Data\Input\Contact;
use DenLopes\Waha\Data\Input\LinkPreview;
use DenLopes\Waha\Data\Input\MessagePoll;
use DenLopes\Waha\Data\Input\RemoteFile;
use DenLopes\Waha\Data\Input\SendListMessage;
use DenLopes\Waha\Data\Input\VCardContact;
use DenLopes\Waha\Data\Input\VideoBinaryFile;
use DenLopes\Waha\Data\Input\VideoRemoteFile;
use DenLopes\Waha\Data\Input\VoiceBinaryFile;
use DenLopes\Waha\Data\Input\VoiceRemoteFile;
use DenLopes\Waha\Data\Output\MessageData;
use DenLopes\Waha\Services\ChatsService;
use DenLopes\Waha\Services\MessagingService;
use DenLopes\Waha\Session;
use DenLopes\Waha\Support\ConversationFactory;
use DenLopes\Waha\Support\Pacing;

/**
 * A fluent, resource-style wrapper around a single WhatsApp chat.
 *
 * The chat knows its session and chat ID, so chat-level actions never require
 * callers to repeat those identifiers. Every action that sends a message returns
 * a {@see Message} handle so message-scoped actions can be chained directly,
 * while every state-changing action returns `$this` so chat actions keep flowing:
 *
 *     $chat->startTyping()
 *         ->sendMessage('Hello!')
 *         ->react('👍');
 *
 * The optional service constructor arguments exist purely for tests: inject
 * mocks when you do not want to hit the real WAHA API.
 */
final class Chat implements ChatContract
{
    public function __construct(
        private readonly Session $session,
        private readonly string $chatId,
        private readonly MessagingService $messaging,
        private readonly ChatsService $chats,
        private readonly ConversationFactory $conversations,
    ) {}

    public function session(): Session
    {
        return $this->session;
    }

    public function chatId(): string
    {
        return $this->chatId;
    }

    /**
     * Get a lazy message handle for this chat (no network call is made until
     * you call {@see Message::get()}).
     */
    public function message(string $id): Message
    {
        return new Message($this->session(), $this->chatId(), $id, $this->chats(), $this->messaging());
    }

    /**
     * Get a human-like conversation handle for this chat (no network call is made).
     */
    public function conversation(?Pacing $policy = null): Conversation
    {
        return $this->conversations->make($this, $policy);
    }

    /**
     * Send a text message and return it as a fluent {@see Message}.
     */
    public function sendMessage(
        string $text,
        ?string $replyTo = null,
        ?bool $linkPreview = true,
        ?string $id = null,
        ?bool $linkPreviewHighQuality = false,
    ): Message {
        $message = $this->messaging()->sendText(
            chatId: $this->chatId(),
            text: $text,
            session: $this->session(),
            replyTo: $replyTo,
            linkPreview: $linkPreview,
            id: $id,
            linkPreviewHighQuality: $linkPreviewHighQuality,
        );

        return $this->wrap($message);
    }

    /**
     * Send an image (by URL or base64 data).
     */
    public function sendImage(
        RemoteFile|BinaryFile $file,
        ?string $caption = null,
        ?string $replyTo = null,
    ): Message {
        return $this->wrap(
            $this->messaging()->sendImage(
                chatId: $this->chatId(),
                file: $file,
                caption: $caption,
                session: $this->session(),
                replyTo: $replyTo,
            ),
        );
    }

    /**
     * Send a file (by URL or base64 data).
     */
    public function sendFile(
        RemoteFile|BinaryFile $file,
        ?string $caption = null,
        ?string $replyTo = null,
    ): Message {
        return $this->wrap(
            $this->messaging()->sendFile(
                chatId: $this->chatId(),
                file: $file,
                caption: $caption,
                session: $this->session(),
                replyTo: $replyTo,
            ),
        );
    }

    /**
     * Send a voice message (by URL or base64 data).
     */
    public function sendVoice(
        VoiceRemoteFile|VoiceBinaryFile $file,
        bool $convert = true,
        ?string $replyTo = null,
    ): Message {
        return $this->wrap(
            $this->messaging()->sendVoice(
                chatId: $this->chatId(),
                file: $file,
                convert: $convert,
                session: $this->session(),
                replyTo: $replyTo,
            ),
        );
    }

    /**
     * Send a video (by URL or base64 data).
     */
    public function sendVideo(
        VideoRemoteFile|VideoBinaryFile $file,
        bool $convert = true,
        ?string $caption = null,
        ?string $replyTo = null,
        ?bool $asNote = null,
    ): Message {
        return $this->wrap(
            $this->messaging()->sendVideo(
                chatId: $this->chatId(),
                file: $file,
                convert: $convert,
                caption: $caption,
                session: $this->session(),
                replyTo: $replyTo,
                asNote: $asNote,
            ),
        );
    }

    /**
     * Send a poll.
     */
    public function sendPoll(MessagePoll $poll, ?string $replyTo = null, ?string $id = null): Message
    {
        return $this->wrap(
            $this->messaging()->sendPoll(
                chatId: $this->chatId(),
                poll: $poll,
                session: $this->session(),
                replyTo: $replyTo,
                id: $id,
            ),
        );
    }

    /**
     * Send a location.
     */
    public function sendLocation(
        float $latitude,
        float $longitude,
        string $title,
        ?string $replyTo = null,
        ?string $id = null,
    ): Message {
        return $this->wrap(
            $this->messaging()->sendLocation(
                chatId: $this->chatId(),
                latitude: $latitude,
                longitude: $longitude,
                title: $title,
                session: $this->session(),
                replyTo: $replyTo,
                id: $id,
            ),
        );
    }

    /**
     * Send one or more contacts as vCards.
     *
     * @param  array<int, Contact|VCardContact>  $contacts
     */
    public function sendContactVcard(array $contacts, ?string $replyTo = null, ?string $id = null): Message
    {
        return $this->wrap(
            $this->messaging()->sendContactVcard(
                chatId: $this->chatId(),
                contacts: $contacts,
                session: $this->session(),
                replyTo: $replyTo,
                id: $id,
            ),
        );
    }

    /**
     * Send an interactive list message.
     */
    public function sendList(SendListMessage $message, ?string $replyTo = null): Message
    {
        return $this->wrap(
            $this->messaging()->sendList(
                chatId: $this->chatId(),
                message: $message,
                session: $this->session(),
                replyTo: $replyTo,
            ),
        );
    }

    /**
     * Send a text message with a custom link preview.
     */
    public function sendLinkCustomPreview(
        string $text,
        LinkPreview $preview,
        ?string $replyTo = null,
        ?bool $linkPreviewHighQuality = true,
    ): Message {
        return $this->wrap(
            $this->messaging()->sendLinkCustomPreview(
                chatId: $this->chatId(),
                text: $text,
                preview: $preview,
                session: $this->session(),
                replyTo: $replyTo,
                linkPreviewHighQuality: $linkPreviewHighQuality,
            ),
        );
    }

    /**
     * Start the typing indicator in this chat.
     */
    public function startTyping(): static
    {
        $this->messaging()->startTyping($this->chatId(), $this->session());

        return $this;
    }

    /**
     * Stop the typing indicator in this chat.
     */
    public function stopTyping(): static
    {
        $this->messaging()->stopTyping($this->chatId(), $this->session());

        return $this;
    }

    /**
     * Forward a message into this chat and return it as a fluent {@see Message}.
     */
    public function forward(string $messageId, ?string $id = null): Message
    {
        $message = $this->messaging()->forwardMessage($this->chatId(), $messageId, $this->session(), $id);

        return $this->wrap($message);
    }

    /**
     * React to a message in this chat with an emoji (empty string removes it).
     */
    public function react(string $messageId, string $reaction): static
    {
        $this->messaging()->setReaction($messageId, $reaction, $this->session());

        return $this;
    }

    /**
     * Star or unstar a message in this chat.
     */
    public function star(string $messageId, bool $star): static
    {
        $this->messaging()->setStar($messageId, $this->chatId(), $star, $this->session());

        return $this;
    }

    /**
     * Fetch a single message and return it as a fluent {@see Message}.
     */
    public function find(string $id): Message
    {
        $message = $this->chats()->getChatMessage($this->session(), $this->chatId(), $id);

        return $this->wrap($message);
    }

    /**
     * List messages in the chat as fluent {@see Message} objects.
     *
     * @return Message[]
     */
    public function getMessages(?int $limit = 10, ?int $offset = null): array
    {
        $messages = $this->chats()->getChatMessages($this->session(), $this->chatId(), $limit, $offset);

        return array_map(
            fn (MessageData $message) => $this->wrap($message),
            $messages,
        );
    }

    /**
     * Mark messages in the chat as read (all messages when none are provided).
     *
     * @param  string[]|null  $messageIds
     */
    public function markRead(?array $messageIds = null, ?string $participant = null): static
    {
        $this->messaging()->sendSeen($this->chatId(), $this->session(), $messageIds, $participant);

        return $this;
    }

    /**
     * Pin a message in this chat.
     */
    public function pinMessage(string $messageId, int $duration = 86400): static
    {
        $this->chats()->pinMessage($this->session(), $this->chatId(), $messageId, $duration);

        return $this;
    }

    /**
     * Unpin a message in this chat.
     */
    public function unpinMessage(string $messageId): static
    {
        $this->chats()->unpinMessage($this->session(), $this->chatId(), $messageId);

        return $this;
    }

    /**
     * Archive the chat.
     */
    public function archive(): static
    {
        $this->chats()->archiveChat($this->session(), $this->chatId());

        return $this;
    }

    /**
     * Unarchive the chat.
     */
    public function unarchive(): static
    {
        $this->chats()->unarchiveChat($this->session(), $this->chatId());

        return $this;
    }

    /**
     * Mark the chat as unread.
     */
    public function markUnread(): static
    {
        $this->chats()->unreadChat($this->session(), $this->chatId());

        return $this;
    }

    /**
     * Clear all messages from the chat.
     */
    public function clearMessages(): static
    {
        $this->chats()->clearMessages($this->session(), $this->chatId());

        return $this;
    }

    /**
     * Delete the chat.
     */
    public function delete(): static
    {
        $this->chats()->deleteChat($this->session(), $this->chatId());

        return $this;
    }

    /**
     * Wrap a typed message into a fluent handle.
     */
    private function wrap(MessageData $message): Message
    {
        return Message::fromData(
            $this->session(),
            $this->chatId(),
            $message,
            $this->chats(),
            $this->messaging(),
        );
    }

    private function messaging(): MessagingService
    {
        return $this->messaging;
    }

    private function chats(): ChatsService
    {
        return $this->chats;
    }
}
