<?php

declare(strict_types=1);

namespace DenLopes\Waha\Tests;

use DenLopes\Waha\Contracts\CircuitBreaker;
use DenLopes\Waha\Contracts\ColdTargetLimiter;
use DenLopes\Waha\Contracts\ContactStageStore;
use DenLopes\Waha\Contracts\ConversationStateStore;
use DenLopes\Waha\Contracts\ReachoutGuard;
use DenLopes\Waha\Contracts\SessionRateLimiter;
use DenLopes\Waha\Contracts\WarmupTracker;
use DenLopes\Waha\Enums\ContactStage;
use DenLopes\Waha\Exceptions\CircuitBreakerOpenException;
use DenLopes\Waha\Exceptions\ColdFanoutThrottledException;
use DenLopes\Waha\Exceptions\ColdMessageContainsUrlException;
use DenLopes\Waha\Exceptions\ConversationThrottledException;
use DenLopes\Waha\Exceptions\SessionRateLimitedException;
use DenLopes\Waha\Resources\Chat;
use DenLopes\Waha\Resources\Conversation;
use DenLopes\Waha\Services\ChatsService;
use DenLopes\Waha\Services\MessagingService;
use DenLopes\Waha\Session;
use DenLopes\Waha\Support\ArrayColdTargetLimiter;
use DenLopes\Waha\Support\ArraySessionRateLimiter;
use DenLopes\Waha\Support\ConversationFactory;
use DenLopes\Waha\Support\Pacing;
use DenLopes\Waha\Support\TierConfig;
use DenLopes\Waha\Tests\Support\FakeCircuitBreaker;
use DenLopes\Waha\Tests\Support\FakeContactStageStore;
use DenLopes\Waha\Tests\Support\FakeConversationStateStore;
use DenLopes\Waha\Tests\Support\FakeReachoutGuard;
use DenLopes\Waha\Tests\Support\FakeWahaClient;
use DenLopes\Waha\Tests\Support\FakeWarmupTracker;

final class WahaConversationPipelineTest extends WahaTestCase
{
    private FakeContactStageStore $contactStageStore;

    private FakeReachoutGuard $reachoutGuard;

    private FakeWarmupTracker $warmupTracker;

    private FakeCircuitBreaker $circuitBreaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contactStageStore = new FakeContactStageStore;
        $this->reachoutGuard = new FakeReachoutGuard;
        $this->warmupTracker = new FakeWarmupTracker;
        $this->circuitBreaker = new FakeCircuitBreaker;

        $this->app->instance(ConversationStateStore::class, new FakeConversationStateStore);
        $this->app->instance(ContactStageStore::class, $this->contactStageStore);
        $this->app->instance(SessionRateLimiter::class, new ArraySessionRateLimiter);
        $this->app->instance(ColdTargetLimiter::class, new ArrayColdTargetLimiter);
        $this->app->instance(ReachoutGuard::class, $this->reachoutGuard);
        $this->app->instance(WarmupTracker::class, $this->warmupTracker);
        $this->app->instance(CircuitBreaker::class, $this->circuitBreaker);
    }

    public function test_cold_send_forces_link_previews_off_and_records_reachout(): void
    {
        [$conversation, $fake] = $this->conversation();

        $conversation->send('hi https://example.com');

        $this->assertFalse($fake->requests[0]['payload']['linkPreview']);
        $this->assertFalse($fake->requests[0]['payload']['linkPreviewHighQuality']);
        $this->assertSame(['default'], $this->reachoutGuard->asserted);
        $this->assertSame(['default'], $this->reachoutGuard->coldSent);
    }

    public function test_cold_url_throws_when_configured(): void
    {
        config(['waha.conversations.reachout.throw_on_cold_urls' => true]);

        [$conversation] = $this->conversation();

        $this->expectException(ColdMessageContainsUrlException::class);
        $conversation->send('check https://example.com');
    }

    public function test_warm_contact_skips_reachout_and_keeps_link_preview(): void
    {
        $this->contactStageStore->stages['default:11111111111@c.us'] = ContactStage::Warm;

        [$conversation, $fake] = $this->conversation();

        $conversation->send('hi');

        $this->assertSame([], $this->reachoutGuard->asserted);
        $this->assertSame([], $this->reachoutGuard->coldSent);
        $this->assertTrue($fake->requests[0]['payload']['linkPreview']);
    }

    public function test_reply_marks_contact_warm(): void
    {
        [$conversation] = $this->conversation();

        $conversation->reply('hi back', 'msg-1');

        $this->assertSame(ContactStage::Warm, $this->contactStageStore->stages['default:11111111111@c.us']);
    }

    public function test_with_stage_override_forces_cold(): void
    {
        [$conversation] = $this->conversation();

        $conversation->withStage(ContactStage::Cold)->send('hi');

        $this->assertSame(['default'], $this->reachoutGuard->asserted);
    }

    public function test_circuit_breaker_open_throws(): void
    {
        $this->circuitBreaker->open = true;

        [$conversation] = $this->conversation();

        $this->expectException(CircuitBreakerOpenException::class);
        $conversation->send('hi');
    }

    public function test_cold_burns_unique_target_budget_but_warm_does_not(): void
    {
        $policy = $this->policyWith(new TierConfig(sessionMaxUniqueTargets: 1, sessionWindowSeconds: 3600));

        [$first] = $this->conversation($policy, '11111111111@c.us');
        [$second] = $this->conversation($policy, '22222222222@c.us');

        $first->send('hi');

        $this->expectException(ColdFanoutThrottledException::class);
        $second->send('hi');
    }

    public function test_warm_contact_does_not_burn_cold_budget(): void
    {
        $policy = $this->policyWith(new TierConfig(sessionMaxUniqueTargets: 1, sessionWindowSeconds: 3600));

        $this->contactStageStore->stages['default:11111111111@c.us'] = ContactStage::Warm;
        [$first] = $this->conversation($policy, '11111111111@c.us');

        $first->send('first');
        $first->send('second');

        $this->assertSame([], $this->reachoutGuard->asserted);
        $this->assertSame([], $this->reachoutGuard->coldSent);
    }

    public function test_session_limiter_enforces_stage_quota(): void
    {
        $policy = $this->policyWith(new TierConfig(sessionMaxMessages: 1, sessionWindowSeconds: 3600));

        [$conversation] = $this->conversation($policy);

        $conversation->send('first');

        $this->expectException(SessionRateLimitedException::class);
        $conversation->send('second');
    }

    public function test_per_chat_window_limit_throws(): void
    {
        $policy = $this->policyWith(new TierConfig(maxMessagesPerWindow: 1, windowSeconds: 60));

        [$conversation] = $this->conversation($policy);

        $conversation->send('first');

        $this->expectException(ConversationThrottledException::class);
        $conversation->send('second');
    }

    /**
     * @return array{Conversation, FakeWahaClient}
     */
    private function conversation(?Pacing $policy = null, string $chatId = '11111111111@c.us'): array
    {
        $fake = new FakeWahaClient([
            'id'     => 'false_11111111111@c.us_ABC',
            'fromMe' => true,
            'body'   => 'Hello',
        ]);

        $factory = $this->app->make(ConversationFactory::class);

        $chat = new Chat(
            Session::from('default'),
            $chatId,
            new MessagingService($fake),
            new ChatsService($fake),
            $factory,
        );

        return [$chat->conversation($policy ?? Pacing::off()), $fake];
    }

    private function policyWith(TierConfig $cold): Pacing
    {
        return new Pacing(
            humanize: false,
            tiers: [
                ContactStage::Cold->value  => $cold,
                ContactStage::Warm->value  => new TierConfig,
                ContactStage::Reply->value => new TierConfig,
            ],
        );
    }
}
