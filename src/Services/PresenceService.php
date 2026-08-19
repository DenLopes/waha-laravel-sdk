<?php

declare(strict_types=1);

namespace DenLopes\Waha\Services;

use DenLopes\Waha\Concerns\SendsRequests;
use DenLopes\Waha\Data\Output\ChatPresences;
use DenLopes\Waha\Enums\PresenceStatus;
use DenLopes\Waha\Session;

class PresenceService
{
    use SendsRequests;

    /**
     * Set session presence (online, offline, typing, recording, paused).
     */
    public function setPresence(
        PresenceStatus $presence,
        ?string $chatId = null,
        ?Session $session = null,
    ): array {
        $payload = ['presence' => $presence->value];

        if ($chatId !== null) {
            $payload['chatId'] = $chatId;
        }

        return $this->send('post', '/api/{session}/presence', $payload, 'Communication with WAHA failed while setting presence.', session: $session);
    }

    /**
     * Get the presence for a chat (also subscribes to it).
     */
    public function getPresence(Session $session, string $chatId): ChatPresences
    {
        $data = $this->send('get', "/api/{session}/presence/{$chatId}", [], 'Communication with WAHA failed while fetching presence.', session: $session);

        return ChatPresences::fromArray($data);
    }

    /**
     * Get all subscribed presence information.
     */
    public function getPresenceAll(Session $session): array
    {
        $data = $this->send('get', '/api/{session}/presence', [], 'Communication with WAHA failed while fetching presence.', session: $session);

        return array_map(
            static fn (array $item) => ChatPresences::fromArray($item),
            $data,
        );
    }

    /**
     * Subscribe to presence events for a chat.
     */
    public function subscribePresence(Session $session, string $chatId): array
    {
        return $this->send('post', "/api/{session}/presence/{$chatId}/subscribe", [], 'Communication with WAHA failed while subscribing to presence.', session: $session);
    }
}
