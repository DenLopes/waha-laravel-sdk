<?php

declare(strict_types=1);

namespace DenLopes\Waha\Services;

use DenLopes\Waha\Concerns\SendsWahaRequests;
use DenLopes\Waha\Data\Output\WAHAChatPresencesData;
use DenLopes\Waha\Enums\WahaPresenceEnum;
use DenLopes\Waha\Support\WahaSession;

class PresenceService
{
    use SendsWahaRequests;

    /**
     * Set session presence (online, offline, typing, recording, paused).
     */
    public function setPresence(
        WahaPresenceEnum $presence,
        ?string $chatId = null,
        ?WahaSession $session = null,
    ): array {
        $payload = ['presence' => $presence->value];

        if ($chatId !== null) {
            $payload['chatId'] = $chatId;
        }

        return $this->send('post', "/api/{$this->session($session)}/presence", $payload, 'Communication with WAHA failed while setting presence.');
    }

    /**
     * Get the presence for a chat (also subscribes to it).
     */
    public function getPresence(WahaSession $session, string $chatId): WAHAChatPresencesData
    {
        $data = $this->send('get', "/api/{$this->session($session)}/presence/{$chatId}", [], 'Communication with WAHA failed while fetching presence.');

        return WAHAChatPresencesData::fromArray($data);
    }

    /**
     * Get all subscribed presence information.
     */
    public function getPresenceAll(WahaSession $session): array
    {
        $data = $this->send('get', "/api/{$this->session($session)}/presence", [], 'Communication with WAHA failed while fetching presence.');

        return array_map(
            static fn (array $item) => WAHAChatPresencesData::fromArray($item),
            $data,
        );
    }

    /**
     * Subscribe to presence events for a chat.
     */
    public function subscribePresence(WahaSession $session, string $chatId): array
    {
        return $this->send('post', "/api/{$this->session($session)}/presence/{$chatId}/subscribe", [], 'Communication with WAHA failed while subscribing to presence.');
    }
}
