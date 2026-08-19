<?php

declare(strict_types=1);

namespace DenLopes\Waha\Services;

use DenLopes\Waha\Concerns\SendsRequests;
use DenLopes\Waha\Data\Input\VideoFile;
use DenLopes\Waha\Data\Input\VoiceFile;
use DenLopes\Waha\Data\Output\Base64File;
use DenLopes\Waha\Session;

class MediaService
{
    use SendsRequests;

    /**
     * Convert a voice file to the WhatsApp (opus) format.
     *
     * WAHA selects the response representation via the `Accept` header: the
     * binary request below returns `audio/ogg`, while the JSON request returns
     * the {@see Base64File} form. There is no `format` query parameter for
     * this endpoint.
     *
     * @param  bool  $asBase64  When true, request/parse the base64 JSON form instead
     *                          of the default binary response.
     */
    public function convertVoice(Session $session, VoiceFile $file, bool $asBase64 = false): string|Base64File
    {
        $endpoint = '/api/{session}/media/convert/voice';

        if ($asBase64) {
            $data = $this->send(
                'post',
                $endpoint,
                $file->toArray(),
                'Communication with WAHA failed while converting the voice file.',
                session: $session,
            );

            return Base64File::fromArray($data);
        }

        return $this->downloadPost(
            $endpoint,
            $file->toArray(),
            'Communication with WAHA failed while converting the voice file.',
            'audio/ogg',
            session: $session,
        );
    }

    /**
     * Convert a video file to the WhatsApp (mp4) format.
     *
     * WAHA selects the response representation via the `Accept` header: the
     * binary request below returns `video/mp4`, while the JSON request returns
     * the {@see Base64File} form. There is no `format` query parameter for
     * this endpoint.
     *
     * @param  bool  $asBase64  When true, request/parse the base64 JSON form instead
     *                          of the default binary response.
     */
    public function convertVideo(Session $session, VideoFile $file, bool $asBase64 = false): string|Base64File
    {
        $endpoint = '/api/{session}/media/convert/video';

        if ($asBase64) {
            $data = $this->send(
                'post',
                $endpoint,
                $file->toArray(),
                'Communication with WAHA failed while converting the video file.',
                session: $session,
            );

            return Base64File::fromArray($data);
        }

        return $this->downloadPost(
            $endpoint,
            $file->toArray(),
            'Communication with WAHA failed while converting the video file.',
            'video/mp4',
            session: $session,
        );
    }
}
