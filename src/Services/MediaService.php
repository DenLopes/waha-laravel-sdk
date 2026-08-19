<?php

declare(strict_types=1);

namespace DenLopes\Waha\Services;

use DenLopes\Waha\Concerns\SendsWahaRequests;
use DenLopes\Waha\Data\Input\VideoFileData;
use DenLopes\Waha\Data\Input\VoiceFileData;
use DenLopes\Waha\Data\Output\Base64FileData;
use DenLopes\Waha\Support\WahaSession;

class MediaService
{
    use SendsWahaRequests;

    /**
     * Convert a voice file to the WhatsApp (opus) format.
     *
     * WAHA selects the response representation via the `Accept` header: the
     * binary request below returns `audio/ogg`, while the JSON request returns
     * the {@see Base64FileData} form. There is no `format` query parameter for
     * this endpoint.
     *
     * @param  bool  $asBase64  When true, request/parse the base64 JSON form instead
     *                          of the default binary response.
     */
    public function convertVoice(WahaSession $session, VoiceFileData $file, bool $asBase64 = false): string|Base64FileData
    {
        $endpoint = "/api/{$this->session($session)}/media/convert/voice";

        if ($asBase64) {
            $data = $this->send(
                'post',
                $endpoint,
                $file->toArray(),
                'Communication with WAHA failed while converting the voice file.',
            );

            return Base64FileData::fromArray($data);
        }

        return $this->downloadPost(
            $endpoint,
            $file->toArray(),
            'Communication with WAHA failed while converting the voice file.',
            'audio/ogg',
        );
    }

    /**
     * Convert a video file to the WhatsApp (mp4) format.
     *
     * WAHA selects the response representation via the `Accept` header: the
     * binary request below returns `video/mp4`, while the JSON request returns
     * the {@see Base64FileData} form. There is no `format` query parameter for
     * this endpoint.
     *
     * @param  bool  $asBase64  When true, request/parse the base64 JSON form instead
     *                          of the default binary response.
     */
    public function convertVideo(WahaSession $session, VideoFileData $file, bool $asBase64 = false): string|Base64FileData
    {
        $endpoint = "/api/{$this->session($session)}/media/convert/video";

        if ($asBase64) {
            $data = $this->send(
                'post',
                $endpoint,
                $file->toArray(),
                'Communication with WAHA failed while converting the video file.',
            );

            return Base64FileData::fromArray($data);
        }

        return $this->downloadPost(
            $endpoint,
            $file->toArray(),
            'Communication with WAHA failed while converting the video file.',
            'video/mp4',
        );
    }
}
