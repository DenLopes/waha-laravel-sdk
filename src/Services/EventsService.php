<?php

declare(strict_types=1);

namespace DenLopes\Waha\Services;

use DenLopes\Waha\Concerns\SendsWahaRequests;
use DenLopes\Waha\Data\Input\EventMessageData;
use DenLopes\Waha\Data\Output\WAMessageData;
use DenLopes\Waha\Support\WahaSession;

class EventsService
{
    use SendsWahaRequests;

    /**
     * Send an event message.
     */
    public function sendEvent(
        WahaSession $session,
        string $chatId,
        EventMessageData $event,
        ?string $replyTo = null,
    ): WAMessageData {
        $payload = [
            'chatId' => $chatId,
            'event'  => $event->toArray(),
        ];

        if ($replyTo !== null) {
            $payload['reply_to'] = $replyTo;
        }

        $data = $this->send('post', '/api/{session}/events', $payload, 'Communication with WAHA failed while sending the event.', session: $session);

        return WAMessageData::fromArray($data);
    }
}
