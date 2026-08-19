<?php

declare(strict_types=1);

namespace DenLopes\Waha\Services;

use DenLopes\Waha\Concerns\SendsWahaRequests;
use DenLopes\Waha\Data\Input\DeleteStatusData;
use DenLopes\Waha\Data\Input\ImageStatusData;
use DenLopes\Waha\Data\Input\TextStatusData;
use DenLopes\Waha\Data\Input\VideoStatusData;
use DenLopes\Waha\Data\Input\VoiceStatusData;
use DenLopes\Waha\Data\Output\NewMessageIdData;
use DenLopes\Waha\Support\WahaSession;

class StatusService
{
    use SendsWahaRequests;

    /**
     * Send a text status.
     */
    public function sendTextStatus(WahaSession $session, TextStatusData $payload): array
    {
        return $this->send('post', "/api/{$this->session($session)}/status/text", $payload->toArray(), 'Communication with WAHA failed while sending the text status.');
    }

    /**
     * Send an image status.
     */
    public function sendImageStatus(WahaSession $session, ImageStatusData $payload): array
    {
        return $this->send('post', "/api/{$this->session($session)}/status/image", $payload->toArray(), 'Communication with WAHA failed while sending the image status.');
    }

    /**
     * Send a voice status.
     */
    public function sendVoiceStatus(WahaSession $session, VoiceStatusData $payload): array
    {
        return $this->send('post', "/api/{$this->session($session)}/status/voice", $payload->toArray(), 'Communication with WAHA failed while sending the voice status.');
    }

    /**
     * Send a video status.
     */
    public function sendVideoStatus(WahaSession $session, VideoStatusData $payload): array
    {
        return $this->send('post', "/api/{$this->session($session)}/status/video", $payload->toArray(), 'Communication with WAHA failed while sending the video status.');
    }

    /**
     * Delete a sent status.
     */
    public function deleteStatus(WahaSession $session, DeleteStatusData $payload): array
    {
        return $this->send('post', "/api/{$this->session($session)}/status/delete", $payload->toArray(), 'Communication with WAHA failed while deleting the status.');
    }

    /**
     * Generate a status message ID that can be used to batch contacts.
     */
    public function getNewStatusMessageId(WahaSession $session): NewMessageIdData
    {
        $data = $this->send('get', "/api/{$this->session($session)}/status/new-message-id", [], 'Communication with WAHA failed while generating the status message ID.');

        return NewMessageIdData::fromArray($data);
    }
}
