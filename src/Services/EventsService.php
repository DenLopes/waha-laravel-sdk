<?php

declare(strict_types=1);

namespace DenLopes\Waha\Services;

use DenLopes\Waha\Concerns\SendsRequests;
use DenLopes\Waha\Data\Input\EventMessage;
use DenLopes\Waha\Data\Output\MessageData;
use DenLopes\Waha\Session;

class EventsService
{
    use SendsRequests;

    /**
     * Send an event message.
     */
    public function sendEvent(
        Session $session,
        string $chatId,
        EventMessage $event,
        ?string $replyTo = null,
    ): MessageData {
        $payload = [
            'chatId' => $chatId,
            'event'  => $event->toArray(),
        ];

        if ($replyTo !== null) {
            $payload['reply_to'] = $replyTo;
        }

        $data = $this->send('post', '/api/{session}/events', $payload, 'Communication with WAHA failed while sending the event.', session: $session);

        return MessageData::fromArray($data);
    }
}
