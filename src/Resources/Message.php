<?php

declare(strict_types=1);

namespace DenLopes\Waha\Resources;

use DenLopes\Waha\Contracts\Message as MessageContract;
use DenLopes\Waha\Data\Output\MessageData;
use DenLopes\Waha\Services\ChatsService;
use DenLopes\Waha\Services\MessagingService;
use DenLopes\Waha\Session;

/**
 * A fluent, resource-style wrapper around a single WhatsApp message.
 *
 * Instances are cheap value/aggregate objects that know their session, chat and
 * message ID, and lazily resolve the services they need from the Laravel
 * container. The optional service constructor arguments exist purely for tests:
 * inject mocks when you do not want to hit the real WAHA API.
 */
final class Message implements MessageContract
{
    private ?MessageData $snapshot = null;

    public function __construct(
        private readonly Session $session,
        private readonly string $chatId,
        private readonly string $id,
        private readonly ChatsService $chats,
        private readonly MessagingService $messaging,
    ) {}

    /**
     * Create a message from an already-fetched WAHA payload.
     */
    public static function fromData(
        Session $session,
        string $chatId,
        MessageData $message,
        ChatsService $chats,
        MessagingService $messaging,
    ): self {
        $instance = new self($session, $chatId, $message->id, $chats, $messaging);
        $instance->snapshot = $message;

        return $instance;
    }

    public function session(): Session
    {
        return $this->session;
    }

    public function chatId(): string
    {
        return $this->chatId;
    }

    public function id(): string
    {
        return $this->id;
    }

    /**
     * Fetch the current message data (uses the snapshot when available).
     */
    public function get(): MessageData
    {
        return $this->snapshot ??= $this->chats()->getChatMessage($this->session(), $this->chatId(), $this->id());
    }

    /**
     * Return the message data as a raw associative array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->get()->toArray();
    }

    /**
     * Return the message data as a JSON string.
     */
    public function toJson(int $flags = 0): string
    {
        return $this->get()->toJson($flags);
    }

    /**
     * Re-fetch the message data from WAHA, discarding the cached snapshot.
     */
    public function refresh(): static
    {
        $this->snapshot = $this->chats()->getChatMessage($this->session(), $this->chatId(), $this->id());

        return $this;
    }

    /**
     * Mark the message as read.
     */
    public function markRead(): static
    {
        $this->messaging()->sendSeen($this->chatId(), $this->session(), [$this->id()]);

        return $this;
    }

    /**
     * React to the message with an emoji (empty string removes the reaction).
     */
    public function react(string $reaction): static
    {
        $this->messaging()->setReaction($this->id(), $reaction, $this->session());

        return $this;
    }

    /**
     * Star (or unstar) the message.
     */
    public function star(bool $star = true): static
    {
        $this->messaging()->setStar($this->id(), $this->chatId(), $star, $this->session());

        return $this;
    }

    /**
     * Pin the message in its chat.
     */
    public function pin(int $duration = 86400): static
    {
        $this->chats()->pinMessage($this->session(), $this->chatId(), $this->id(), $duration);

        return $this;
    }

    /**
     * Unpin the message from its chat.
     */
    public function unpin(): static
    {
        $this->chats()->unpinMessage($this->session(), $this->chatId(), $this->id());

        return $this;
    }

    /**
     * Forward this message into another chat and return the new message.
     */
    public function forward(string $chatId, ?string $id = null): Message
    {
        $message = $this->messaging()->forwardMessage($chatId, $this->id(), $this->session(), $id);

        return self::fromData($this->session(), $chatId, $message, $this->chats(), $this->messaging());
    }

    /**
     * Update the message text (and optionally its link preview behavior).
     */
    public function update(
        string $text,
        ?bool $linkPreview = null,
        ?bool $linkPreviewHighQuality = null,
    ): static {
        $this->chats()->editMessage(
            $this->session(),
            $this->chatId(),
            $this->id(),
            $text,
            $linkPreview,
            $linkPreviewHighQuality,
        );

        // The cached snapshot is now stale.
        $this->snapshot = null;

        return $this;
    }

    /**
     * Delete the message.
     */
    public function delete(): static
    {
        $this->chats()->deleteMessage($this->session(), $this->chatId(), $this->id());

        return $this;
    }

    private function chats(): ChatsService
    {
        return $this->chats;
    }

    private function messaging(): MessagingService
    {
        return $this->messaging;
    }
}
