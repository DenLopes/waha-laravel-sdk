<?php

declare(strict_types=1);

namespace DenLopes\Waha\Tests;

use DenLopes\Waha\Contracts\ConversationStateStore;
use DenLopes\Waha\Contracts\SessionRateLimiter;
use DenLopes\Waha\Enums\ContactStage;
use DenLopes\Waha\Exceptions\SessionRateLimitedException;
use DenLopes\Waha\Support\CacheConversationStateStore;
use DenLopes\Waha\Support\PacingState;
use DenLopes\Waha\Support\TierConfig;
use Illuminate\Support\Facades\Cache;

final class WahaConversationStateTest extends WahaTestCase
{
    public function test_store_persists_and_forgets_state(): void
    {
        $store = $this->app->make(ConversationStateStore::class);

        $state = new PacingState(lastSentAt: 123.0, sentAt: [1.0, 2.0]);

        $store->put('default:123@c.us', $state, 60);

        $this->assertEquals($state, $store->get('default:123@c.us'));

        $store->forget('default:123@c.us');

        $this->assertNull($store->get('default:123@c.us'));
    }

    public function test_default_store_is_cache_backed(): void
    {
        $store = $this->app->make(ConversationStateStore::class);

        $this->assertInstanceOf(CacheConversationStateStore::class, $store);
    }

    public function test_lock_runs_callback_and_releases(): void
    {
        $store = $this->app->make(ConversationStateStore::class);

        $heldDuringCallback = false;

        $result = $store->lock('default:123@c.us', 60, 60, function () use (&$heldDuringCallback): string {
            $locks = Cache::store()->getStore()->locks;
            $heldDuringCallback = isset($locks['waha:conversation:default:123@c.us']);

            return 'sent';
        });

        $this->assertSame('sent', $result);
        $this->assertTrue($heldDuringCallback);
        $this->assertArrayNotHasKey('waha:conversation:default:123@c.us', Cache::store()->getStore()->locks);
    }

    public function test_session_limiter_throws_after_limit(): void
    {
        $limiter = $this->app->make(SessionRateLimiter::class);
        $tier = new TierConfig(sessionMaxMessages: 1, sessionWindowSeconds: 60);

        $limiter->hit('sales', ContactStage::Cold, $tier);

        $this->expectException(SessionRateLimitedException::class);
        $limiter->hit('sales', ContactStage::Cold, $tier);
    }
}
