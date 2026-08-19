<?php

declare(strict_types=1);

namespace DenLopes\Waha\Services;

use DenLopes\Waha\Concerns\SendsRequests;
use DenLopes\Waha\Data\Input\DeleteStatus;
use DenLopes\Waha\Data\Input\ImageStatus;
use DenLopes\Waha\Data\Input\TextStatus;
use DenLopes\Waha\Data\Input\VideoStatus;
use DenLopes\Waha\Data\Input\VoiceStatus;
use DenLopes\Waha\Data\Output\NewMessageId;
use DenLopes\Waha\Session;

class StatusService
{
    use SendsRequests;

    /**
     * Send a text status.
     */
    public function sendTextStatus(Session $session, TextStatus $payload): array
    {
        return $this->send('post', '/api/{session}/status/text', $payload->toArray(), 'Communication with WAHA failed while sending the text status.', session: $session);
    }

    /**
     * Send an image status.
     */
    public function sendImageStatus(Session $session, ImageStatus $payload): array
    {
        return $this->send('post', '/api/{session}/status/image', $payload->toArray(), 'Communication with WAHA failed while sending the image status.', session: $session);
    }

    /**
     * Send a voice status.
     */
    public function sendVoiceStatus(Session $session, VoiceStatus $payload): array
    {
        return $this->send('post', '/api/{session}/status/voice', $payload->toArray(), 'Communication with WAHA failed while sending the voice status.', session: $session);
    }

    /**
     * Send a video status.
     */
    public function sendVideoStatus(Session $session, VideoStatus $payload): array
    {
        return $this->send('post', '/api/{session}/status/video', $payload->toArray(), 'Communication with WAHA failed while sending the video status.', session: $session);
    }

    /**
     * Delete a sent status.
     */
    public function deleteStatus(Session $session, DeleteStatus $payload): array
    {
        return $this->send('post', '/api/{session}/status/delete', $payload->toArray(), 'Communication with WAHA failed while deleting the status.', session: $session);
    }

    /**
     * Generate a status message ID that can be used to batch contacts.
     */
    public function getNewStatusMessageId(Session $session): NewMessageId
    {
        $data = $this->send('get', '/api/{session}/status/new-message-id', [], 'Communication with WAHA failed while generating the status message ID.', session: $session);

        return NewMessageId::fromArray($data);
    }
}
