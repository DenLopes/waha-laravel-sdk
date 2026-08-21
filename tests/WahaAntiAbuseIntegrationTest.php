<?php

declare(strict_types=1);

namespace DenLopes\Waha\Tests;

use DenLopes\Waha\Contracts\ContactStageStore;
use DenLopes\Waha\Data\Output\MessageCapping;
use DenLopes\Waha\Data\Output\ReachoutTimelock;
use DenLopes\Waha\Enums\ContactStage;
use DenLopes\Waha\Exceptions\ReachoutQuotaExhaustedException;
use DenLopes\Waha\Exceptions\ReachoutTimelockActiveException;
use DenLopes\Waha\Support\Pacing;
use DenLopes\Waha\Support\ReachoutGuard;
use DenLopes\Waha\Tests\Support\FakeSessionService;

final class WahaAntiAbuseIntegrationTest extends WahaTestCase
{
    public function test_contact_stage_store_persists_and_forgets(): void
    {
        $store = $this->app->make(ContactStageStore::class);

        $store->mark('default', '11111111111@c.us', ContactStage::Warm);

        $this->assertSame(ContactStage::Warm, $store->get('default', '11111111111@c.us'));

        $store->forget('default', '11111111111@c.us');

        $this->assertNull($store->get('default', '11111111111@c.us'));
    }

    public function test_reachout_guard_fails_open_on_null_quota(): void
    {
        $service = new FakeSessionService;
        $service->capping = new MessageCapping(null, '', null, null, null, null, null, null);

        (new ReachoutGuard($service))->assertAllowed('default');

        $this->addToAssertionCount(1);
    }

    public function test_reachout_guard_fails_open_on_api_exception(): void
    {
        $service = new FakeSessionService;
        $service->throwApiException = true;

        (new ReachoutGuard($service))->assertAllowed('default');

        $this->addToAssertionCount(1);
    }

    public function test_reachout_guard_throws_when_quota_is_zero(): void
    {
        $service = new FakeSessionService;
        $service->capping = new MessageCapping(null, '', 0, 0, null, null, null, null);

        $this->expectException(ReachoutQuotaExhaustedException::class);

        (new ReachoutGuard($service))->assertAllowed('default');
    }

    public function test_reachout_guard_throws_when_quota_is_exhausted(): void
    {
        $service = new FakeSessionService;
        $service->capping = new MessageCapping(null, '', 10, 10, null, null, null, null);

        $this->expectException(ReachoutQuotaExhaustedException::class);

        (new ReachoutGuard($service))->assertAllowed('default');
    }

    public function test_reachout_guard_throws_when_timelock_is_active(): void
    {
        $service = new FakeSessionService;
        $service->timelock = new ReachoutTimelock(null, '', true, null);

        $this->expectException(ReachoutTimelockActiveException::class);

        (new ReachoutGuard($service))->assertAllowed('default');
    }

    public function test_reachout_guard_counts_local_cold_sends_against_quota(): void
    {
        $service = new FakeSessionService;
        $service->capping = new MessageCapping(null, '', 1, 0, null, null, null, null);

        $guard = new ReachoutGuard($service);

        $guard->assertAllowed('default');
        $guard->recordColdSent('default');

        $this->expectException(ReachoutQuotaExhaustedException::class);
        $guard->assertAllowed('default');
    }

    public function test_pacing_from_config_reads_grouped_shape(): void
    {
        config([
            'waha.conversations.pacing.humanize'                    => false,
            'waha.conversations.tiers.cold.max_messages_per_window' => 2,
        ]);

        $policy = Pacing::fromConfig();

        $this->assertFalse($policy->humanize);
        $this->assertSame(2, $policy->tier(ContactStage::Cold)->maxMessagesPerWindow);
    }
}
