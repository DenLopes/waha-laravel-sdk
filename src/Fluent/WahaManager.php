<?php

declare(strict_types=1);

namespace DenLopes\Waha\Fluent;

use DenLopes\Waha\Debug\WahaDebugStore;
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
    /**
     * Get a chat handle (no network call is made).
     */
    public function chat(string $chatId, string|WahaSession|null $session = null): WahaChat
    {
        return new WahaChat($this->resolveSession($session), $chatId);
    }

    /**
     * Get a message handle (no network call is made; use `get()`/`refresh()`).
     */
    public function message(string $chatId, string $id, string|WahaSession|null $session = null): WahaMessage
    {
        return new WahaMessage($this->resolveSession($session), $chatId, $id);
    }

    /**
     * Resolve a session value object from a name (or the configured default).
     */
    public function session(string|WahaSession|null $session = null): WahaSession
    {
        return $this->resolveSession($session);
    }

    /**
     * The last captured request/response, for debugging.
     *
     * @return array<string, mixed>|null
     */
    public function lastHttp(): ?array
    {
        return app(WahaDebugStore::class)->last();
    }

    /**
     * Render the last request as a copy-pasteable curl command.
     */
    public function lastHttpCurl(): ?string
    {
        return app(WahaDebugStore::class)->lastCurl();
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
