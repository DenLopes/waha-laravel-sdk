<?php

declare(strict_types=1);

namespace DenLopes\Waha\Fluent;

use DenLopes\Waha\Contracts\PinStore;
use DenLopes\Waha\Debug\WahaDebugStore;
use DenLopes\Waha\Services\ChatsService;
use DenLopes\Waha\Services\ChattingService;
use DenLopes\Waha\Support\WahaSession;

/**
 * Container-resolvable entry point for the higher-level WAHA resources.
 *
 * It is a thin factory for {@see WahaChat} and {@see WahaMessage} handles and
 * for resolving sessions. Resolve it through the container or inject it:
 *
 *     $waha = app(WahaManager::class);
 *     $chat = $waha->chat('5511999999999@c.us');
 *
 * Sessions can be provided as either a {@see WahaSession} value object or a
 * plain session name; when omitted, the configured default is used:
 *
 *     $chat = $waha->chat('5511999999999@c.us', 'sales');
 *     $chat = $waha->chat('5511999999999@c.us', WahaSession::from('sales'));
 */
final class WahaManager
{
    public function __construct(
        private readonly ChattingService $chatting,
        private readonly ChatsService $chats,
        private readonly PinStore $pins,
        private readonly WahaDebugStore $debug,
    ) {}

    /**
     * Get a chat handle (no network call is made).
     */
    public function chat(string $chatId, string|WahaSession|null $session = null): WahaChat
    {
        return new WahaChat($this->resolveSession($session), $chatId, $this->chatting, $this->chats);
    }

    /**
     * Get a message handle (no network call is made; use `get()`/`refresh()`).
     */
    public function message(string $chatId, string $id, string|WahaSession|null $session = null): WahaMessage
    {
        return new WahaMessage($this->resolveSession($session), $chatId, $id, $this->chats, $this->chatting);
    }

    /**
     * Resolve a session value object from a name (or the configured default).
     */
    public function session(string|WahaSession|null $session = null): WahaSession
    {
        return $this->resolveSession($session);
    }

    /**
     * Pin a session to a specific host.
     *
     * This is the write side of session routing: when `waha.routing.driver` is
     * `pin`, the SDK will resolve this session to the given host on every call.
     * An optional TTL expires the pin automatically (useful during migrations).
     */
    public function pinSession(string $session, string $hostKey, ?int $ttlSeconds = null): static
    {
        $this->pins->pin($session, $hostKey, $ttlSeconds);

        return $this;
    }

    /**
     * Remove a session → host pin.
     */
    public function unpinSession(string $session): static
    {
        $this->pins->forget($session);

        return $this;
    }

    /**
     * The host currently pinned for a session, or null when none is set.
     */
    public function sessionHost(string $session): ?string
    {
        return $this->pins->getHostForSession($session);
    }

    /**
     * The last captured request/response, for debugging.
     *
     * @return array<string, mixed>|null
     */
    public function lastHttp(): ?array
    {
        return $this->debug->last();
    }

    /**
     * Render the last request as a copy-pasteable curl command.
     */
    public function lastHttpCurl(): ?string
    {
        return $this->debug->lastCurl();
    }

    /**
     * Normalize a string name / value object / null into a {@see WahaSession}.
     */
    private function resolveSession(string|WahaSession|null $session): WahaSession
    {
        if ($session instanceof WahaSession) {
            return $session;
        }

        return $session === null ? WahaSession::default() : WahaSession::from($session);
    }
}
