<?php

declare(strict_types=1);

namespace DenLopes\Waha;

use DenLopes\Waha\Resources\Chat;
use DenLopes\Waha\Resources\Conversation;
use DenLopes\Waha\Resources\Message;
use DenLopes\Waha\Services\ChatsService;
use DenLopes\Waha\Services\MessagingService;
use DenLopes\Waha\Support\ConversationFactory;
use DenLopes\Waha\Support\Pacing;

/**
 * Container-resolvable entry point for the higher-level WAHA resources.
 *
 * It is a thin factory for {@see Chat} and {@see Message} handles and
 * for resolving sessions. Resolve it through the container or inject it:
 *
 *     $waha = app(Client::class);
 *     $chat = $waha->chat('5511999999999@c.us');
 *
 * Sessions can be provided as either a {@see Session} value object or a
 * plain session name; when omitted, the configured default is used:
 *
 *     $chat = $waha->chat('5511999999999@c.us', 'sales');
 *     $chat = $waha->chat('5511999999999@c.us', Session::from('sales'));
 */
final class Client
{
    public function __construct(
        private readonly MessagingService $messaging,
        private readonly ChatsService $chats,
        private readonly ConversationFactory $conversations,
    ) {}

    /**
     * Get a chat handle (no network call is made).
     */
    public function chat(string $chatId, string|Session|null $session = null): Chat
    {
        return new Chat($this->resolveSession($session), $chatId, $this->messaging, $this->chats, $this->conversations);
    }

    /**
     * Get a message handle (no network call is made; use `get()`/`refresh()`).
     */
    public function message(string $chatId, string $id, string|Session|null $session = null): Message
    {
        return new Message($this->resolveSession($session), $chatId, $id, $this->chats, $this->messaging);
    }

    /**
     * Get a human-like conversation handle (no network call is made).
     *
     * The anti-ban policy is read from `waha.conversations` unless one is
     * explicitly provided.
     */
    public function conversation(
        string $chatId,
        string|Session|null $session = null,
        ?Pacing $policy = null,
    ): Conversation {
        return $this->chat($chatId, $session)->conversation($policy);
    }

    /**
     * Resolve a session value object from a name (or the configured default).
     */
    public function session(string|Session|null $session = null): Session
    {
        return $this->resolveSession($session);
    }

    /**
     * Normalize a string name / value object / null into a {@see Session}.
     */
    private function resolveSession(string|Session|null $session): Session
    {
        if ($session instanceof Session) {
            return $session;
        }

        return $session === null ? Session::default() : Session::from($session);
    }
}
