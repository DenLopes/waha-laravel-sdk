<?php

declare(strict_types=1);

namespace DenLopes\Waha\Webhooks;

use DenLopes\Waha\Exception\WahaWebhookException;

/**
 * Verifies the WAHA webhook HMAC signature.
 *
 * WAHA signs the raw request body with the configured webhook secret and sends
 * the signature through the `X-Webhook-Hmac` header. The algorithm is taken from
 * the `X-Webhook-Hmac-Algorithm` header and defaults to `sha512`.
 */
final class WebhookVerifier
{
    public const HEADER_HMAC = 'X-Webhook-Hmac';

    public const HEADER_HMAC_ALGO = 'X-Webhook-Hmac-Algorithm';

    public const HEADER_REQUEST_ID = 'X-Webhook-Request-Id';

    public const HEADER_TIMESTAMP = 'X-Webhook-Timestamp';

    /**
     * Legacy/alternate header names kept as a fallback for easier upgrades.
     */
    public const LEGACY_HEADER_HMAC = 'X-Waha-Signature';

    public const LEGACY_HEADER_HMAC_ALGO = 'X-Waha-Signature-Algorithm';

    /**
     * Only allow these algorithms to avoid accepting arbitrary input.
     */
    private const ALLOWED_ALGOS = ['sha256', 'sha512'];

    /**
     * Verify a signature against the raw body.
     *
     * @throws WahaWebhookException When the signature is missing or invalid.
     */
    public function verify(string $secret, string $rawBody, ?string $hmacHeader, ?string $algoHeader = null): void
    {
        if ($secret === '' || $hmacHeader === null || $hmacHeader === '') {
            throw $this->invalidSignature('The webhook secret or HMAC header is missing.');
        }

        $algo = $this->normalizeAlgo($algoHeader);

        if ($algo === null) {
            throw $this->invalidSignature('Unsupported webhook signature algorithm.', [
                'algorithm' => $algoHeader,
            ]);
        }

        $provided = $this->extractSignature($hmacHeader);
        $expected = hash_hmac($algo, $rawBody, $secret);

        if ($provided === '' || ! hash_equals(strtolower($expected), strtolower($provided))) {
            throw $this->invalidSignature('Invalid webhook signature.', [
                'algorithm' => $algo,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function invalidSignature(string $message, array $context = []): WahaWebhookException
    {
        return new WahaWebhookException(
            $message,
            reason: 'invalid_hmac',
            status: 401,
            context: $context,
        );
    }

    private function normalizeAlgo(?string $algoHeader): ?string
    {
        $algo = strtolower(trim((string) $algoHeader));

        // WAHA uses sha512; treat a missing algorithm as sha512.
        if ($algo === '') {
            $algo = 'sha512';
        }

        return in_array($algo, self::ALLOWED_ALGOS, true) ? $algo : null;
    }

    private function extractSignature(string $headerValue): string
    {
        $signature = trim($headerValue);

        // Accept formats such as "sha512=<hex>" or "sha256=<hex>".
        if (str_contains($signature, '=')) {
            $parts = explode('=', $signature, 2);
            $signature = trim($parts[1] ?? '');
        }

        // Keep only hex characters (defensive).
        return preg_replace('/[^a-fA-F0-9]/', '', $signature) ?? '';
    }
}
