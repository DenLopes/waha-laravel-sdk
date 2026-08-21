<?php

declare(strict_types=1);

namespace DenLopes\Waha\Tests;

use DenLopes\Waha\Data\Output\Webhook;
use DenLopes\Waha\Enums\ContactStage;
use DenLopes\Waha\Tests\Support\FakeCircuitBreaker;
use DenLopes\Waha\Tests\Support\FakeContactStageStore;
use DenLopes\Waha\Webhooks\Events\WebhookReceived;
use DenLopes\Waha\Webhooks\Listeners\AntiAbuseWebhookListener;
use PHPUnit\Framework\TestCase;

final class WahaWebhookSubscriberTest extends TestCase
{
    public function test_inbound_message_marks_contact_warm(): void
    {
        $store = new FakeContactStageStore;
        $listener = new AntiAbuseWebhookListener($store, new FakeCircuitBreaker);

        $listener->handle($this->received('{"event":"message","session":"default","payload":{"from":"5511999999999@c.us","fromMe":false,"id":"abc"}}'));

        $this->assertSame(ContactStage::Warm, $store->stages['default:5511999999999@c.us']);
    }

    public function test_group_message_does_not_mark_warm(): void
    {
        $store = new FakeContactStageStore;
        $listener = new AntiAbuseWebhookListener($store, new FakeCircuitBreaker);

        $listener->handle($this->received('{"event":"message","session":"default","payload":{"from":"group@g.us","fromMe":false,"participant":"5511999999999@c.us","id":"abc"}}'));

        $this->assertSame([], $store->marked);
    }

    public function test_outbound_message_does_not_mark_warm(): void
    {
        $store = new FakeContactStageStore;
        $listener = new AntiAbuseWebhookListener($store, new FakeCircuitBreaker);

        $listener->handle($this->received('{"event":"message","session":"default","payload":{"from":"5511999999999@c.us","fromMe":true,"id":"abc"}}'));

        $this->assertSame([], $store->marked);
    }

    public function test_message_ack_error_records_failure(): void
    {
        $breaker = new FakeCircuitBreaker;
        $listener = new AntiAbuseWebhookListener(new FakeContactStageStore, $breaker);

        $listener->handle($this->received('{"event":"message.ack","session":"default","payload":{"ack":-1,"id":"abc"}}'));

        $this->assertSame(['default'], $breaker->failures);
    }

    public function test_message_ack_success_records_success(): void
    {
        $breaker = new FakeCircuitBreaker;
        $listener = new AntiAbuseWebhookListener(new FakeContactStageStore, $breaker);

        $listener->handle($this->received('{"event":"message.ack","session":"default","payload":{"ack":3,"id":"abc"}}'));

        $this->assertSame(['default'], $breaker->successes);
    }

    private function received(string $json): WebhookReceived
    {
        return new WebhookReceived(Webhook::fromJson($json));
    }
}
