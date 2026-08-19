<?php

declare(strict_types=1);

namespace DenLopes\Waha\Services;

use DenLopes\Waha\Concerns\SendsWahaRequests;
use DenLopes\Waha\Data\Input\PasskeyAssertionRequestData;
use DenLopes\Waha\Data\Input\RequestCodeRequestData;
use DenLopes\Waha\Data\Output\Base64FileData;
use DenLopes\Waha\Data\Output\PasskeyChallengeData;
use DenLopes\Waha\Data\Output\PasskeyConfirmationData;
use DenLopes\Waha\Data\Output\QRCodeValueData;
use DenLopes\Waha\Enums\WahaQrFormatEnum;
use DenLopes\Waha\Support\WahaSession;

class PairingService
{
    use SendsWahaRequests;

    /**
     * Get the QR code for pairing a WhatsApp session.
     *
     * @param  WahaSession  $session  Session name.
     * @param  WahaQrFormatEnum  $format  "image" (binary PNG) or "raw" (JSON).
     * @return string|QRCodeValueData Binary PNG when format is image, QR code value otherwise.
     */
    public function getQrCode(
        WahaSession $session,
        WahaQrFormatEnum $format = WahaQrFormatEnum::IMAGE,
    ): string|QRCodeValueData {
        $endpoint = "/api/{$this->session($session)}/auth/qr";

        if ($format === WahaQrFormatEnum::IMAGE) {
            return $this->download(
                $endpoint,
                ['format' => WahaQrFormatEnum::IMAGE->value],
                'Communication with WAHA failed while fetching the QR code.',
                'image/png',
            );
        }

        $data = $this->send('get', $endpoint, ['format' => $format->value], 'Communication with WAHA failed while fetching the QR code.');

        return QRCodeValueData::fromArray($data);
    }

    /**
     * Request an authentication code for phone number pairing.
     */
    public function requestCode(WahaSession $session, RequestCodeRequestData $request): array
    {
        return $this->send(
            'post',
            "/api/{$this->session($session)}/auth/request-code",
            $request->toArray(),
            'Communication with WAHA failed while requesting the authentication code.',
        );
    }

    /**
     * Get the pending passkey (WebAuthn) challenge.
     */
    public function getPasskeyChallenge(WahaSession $session): PasskeyChallengeData
    {
        $data = $this->send(
            'get',
            "/api/{$this->session($session)}/auth/passkey/challenge",
            [],
            'Communication with WAHA failed while fetching the passkey challenge.',
        );

        return PasskeyChallengeData::fromArray($data);
    }

    /**
     * Submit a WebAuthn passkey assertion to finish pairing.
     */
    public function submitPasskey(WahaSession $session, PasskeyAssertionRequestData $assertion): array
    {
        return $this->send(
            'post',
            "/api/{$this->session($session)}/auth/passkey",
            $assertion->toArray(),
            'Communication with WAHA failed while submitting the passkey.',
        );
    }

    /**
     * Get the pending passkey confirmation code.
     */
    public function getPasskeyConfirmation(WahaSession $session): PasskeyConfirmationData
    {
        $data = $this->send(
            'get',
            "/api/{$this->session($session)}/auth/passkey/confirmation",
            [],
            'Communication with WAHA failed while fetching the passkey confirmation.',
        );

        return PasskeyConfirmationData::fromArray($data);
    }

    /**
     * Confirm passkey pairing (only needed for the manual code case).
     */
    public function confirmPasskey(WahaSession $session): array
    {
        return $this->send(
            'post',
            "/api/{$this->session($session)}/auth/passkey/confirm",
            [],
            'Communication with WAHA failed while confirming the passkey.',
        );
    }

    /**
     * Get a screenshot of the current WhatsApp session.
     *
     * WAHA selects the response representation via the `Accept` header: the
     * binary request below returns a JPEG, while the JSON request returns the
     * {@see Base64FileData} form. There is no `format` query parameter for this
     * endpoint.
     *
     * @param  bool  $asBase64  When true, request/parse the base64 JSON form instead
     *                          of the default binary JPEG.
     */
    public function getScreenshot(WahaSession $session, bool $asBase64 = false): string|Base64FileData
    {
        $payload = ['session' => $this->session($session)];

        if ($asBase64) {
            $data = $this->send(
                'get',
                '/api/screenshot',
                $payload,
                'Communication with WAHA failed while fetching the screenshot.',
            );

            return Base64FileData::fromArray($data);
        }

        return $this->download(
            '/api/screenshot',
            $payload,
            'Communication with WAHA failed while fetching the screenshot.',
            'image/jpeg',
        );
    }
}
