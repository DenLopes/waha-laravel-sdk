<?php

declare(strict_types=1);

namespace DenLopes\Waha\Services;

use DenLopes\Waha\Concerns\SendsRequests;
use DenLopes\Waha\Data\Input\PasskeyAssertionRequest;
use DenLopes\Waha\Data\Input\RequestCodeRequest;
use DenLopes\Waha\Data\Output\Base64File;
use DenLopes\Waha\Data\Output\PasskeyChallenge;
use DenLopes\Waha\Data\Output\PasskeyConfirmation;
use DenLopes\Waha\Data\Output\QRCodeValue;
use DenLopes\Waha\Enums\QrFormat;
use DenLopes\Waha\Session;

class PairingService
{
    use SendsRequests;

    /**
     * Get the QR code for pairing a WhatsApp session.
     *
     * @param  Session  $session  Session name.
     * @param  QrFormat  $format  "image" (binary PNG) or "raw" (JSON).
     * @return string|QRCodeValue Binary PNG when format is image, QR code value otherwise.
     */
    public function getQrCode(
        Session $session,
        QrFormat $format = QrFormat::IMAGE,
    ): string|QRCodeValue {
        $endpoint = '/api/{session}/auth/qr';

        if ($format === QrFormat::IMAGE) {
            return $this->download(
                $endpoint,
                ['format' => QrFormat::IMAGE->value],
                'Communication with WAHA failed while fetching the QR code.',
                'image/png',
                session: $session,
            );
        }

        $data = $this->send('get', $endpoint, ['format' => $format->value], 'Communication with WAHA failed while fetching the QR code.', session: $session);

        return QRCodeValue::fromArray($data);
    }

    /**
     * Request an authentication code for phone number pairing.
     */
    public function requestCode(Session $session, RequestCodeRequest $request): array
    {
        return $this->send(
            'post',
            '/api/{session}/auth/request-code',
            $request->toArray(),
            'Communication with WAHA failed while requesting the authentication code.',
            session: $session,
        );
    }

    /**
     * Get the pending passkey (WebAuthn) challenge.
     */
    public function getPasskeyChallenge(Session $session): PasskeyChallenge
    {
        $data = $this->send(
            'get',
            '/api/{session}/auth/passkey/challenge',
            [],
            'Communication with WAHA failed while fetching the passkey challenge.',
            session: $session,
        );

        return PasskeyChallenge::fromArray($data);
    }

    /**
     * Submit a WebAuthn passkey assertion to finish pairing.
     */
    public function submitPasskey(Session $session, PasskeyAssertionRequest $assertion): array
    {
        return $this->send(
            'post',
            '/api/{session}/auth/passkey',
            $assertion->toArray(),
            'Communication with WAHA failed while submitting the passkey.',
            session: $session,
        );
    }

    /**
     * Get the pending passkey confirmation code.
     */
    public function getPasskeyConfirmation(Session $session): PasskeyConfirmation
    {
        $data = $this->send(
            'get',
            '/api/{session}/auth/passkey/confirmation',
            [],
            'Communication with WAHA failed while fetching the passkey confirmation.',
            session: $session,
        );

        return PasskeyConfirmation::fromArray($data);
    }

    /**
     * Confirm passkey pairing (only needed for the manual code case).
     */
    public function confirmPasskey(Session $session): array
    {
        return $this->send(
            'post',
            '/api/{session}/auth/passkey/confirm',
            [],
            'Communication with WAHA failed while confirming the passkey.',
            session: $session,
        );
    }

    /**
     * Get a screenshot of the current WhatsApp session.
     *
     * WAHA selects the response representation via the `Accept` header: the
     * binary request below returns a JPEG, while the JSON request returns the
     * {@see Base64File} form. There is no `format` query parameter for this
     * endpoint.
     *
     * @param  bool  $asBase64  When true, request/parse the base64 JSON form instead
     *                          of the default binary JPEG.
     */
    public function getScreenshot(Session $session, bool $asBase64 = false): string|Base64File
    {
        $payload = ['session' => $this->session($session)];

        if ($asBase64) {
            $data = $this->send(
                'get',
                '/api/screenshot',
                $payload,
                'Communication with WAHA failed while fetching the screenshot.',
            );

            return Base64File::fromArray($data);
        }

        return $this->download(
            '/api/screenshot',
            $payload,
            'Communication with WAHA failed while fetching the screenshot.',
            'image/jpeg',
        );
    }
}
