<?php

declare(strict_types=1);

namespace DenLopes\Waha\Tests;

use DenLopes\Waha\Exception\WahaWebhookException;
use DenLopes\Waha\Webhooks\WebhookGuard;
use DenLopes\Waha\Webhooks\WebhookVerifier;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

final class WahaWebhookTest extends TestCase
{
    public function test_verifier_accepts_valid_sha512_signature(): void
    {
        $secret = 'super-secret';
        $body = '{"event":"message"}';
        $signature = hash_hmac('sha512', $body, $secret);

        (new WebhookVerifier)->verify($secret, $body, $signature, 'sha512');

        $this->addToAssertionCount(1);
    }

    public function test_verifier_rejects_wrong_secret(): void
    {
        $body = '{"event":"message"}';
        $signature = hash_hmac('sha512', $body, 'right-secret');

        $this->expectException(WahaWebhookException::class);
        $this->expectExceptionMessage('Invalid webhook signature.');

        (new WebhookVerifier)->verify('wrong-secret', $body, $signature, 'sha512');
    }

    public function test_verifier_rejects_unknown_algorithm(): void
    {
        $body = '{"event":"message"}';
        $signature = hash_hmac('sha512', $body, 'secret');

        $this->expectException(WahaWebhookException::class);
        $this->expectExceptionMessage('Unsupported webhook signature algorithm.');

        (new WebhookVerifier)->verify('secret', $body, $signature, 'sha1');
    }

    public function test_verifier_accepts_prefixed_signature(): void
    {
        $secret = 'secret';
        $body = 'payload';
        $signature = 'sha512='.hash_hmac('sha512', $body, $secret);

        (new WebhookVerifier)->verify($secret, $body, $signature, 'sha512');

        $this->addToAssertionCount(1);
    }

    public function test_guard_accepts_timestamp_within_window(): void
    {
        $guard = new WebhookGuard(maxClockSkewMs: 300000);

        $guard->assertFreshTimestamp((int) Carbon::now()->getTimestampMs());

        $this->addToAssertionCount(1);
    }

    public function test_guard_rejects_timestamp_outside_window(): void
    {
        $guard = new WebhookGuard(maxClockSkewMs: 1000);

        $this->expectException(WahaWebhookException::class);
        $this->expectExceptionMessage('timestamp is outside the allowed window');

        $guard->assertFreshTimestamp((int) Carbon::now()->subMinutes(10)->getTimestampMs());
    }
}
