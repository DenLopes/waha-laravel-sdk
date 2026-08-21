<?php

declare(strict_types=1);

namespace DenLopes\Waha\Tests;

use DenLopes\Waha\Enums\ContactStage;
use DenLopes\Waha\Support\Pacing;
use DenLopes\Waha\Support\Spintax;
use DenLopes\Waha\Support\TierConfig;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class WahaAntiAbuseUnitTest extends TestCase
{
    public function test_contact_stage_values(): void
    {
        $this->assertSame('cold', ContactStage::Cold->value);
        $this->assertSame('warm', ContactStage::Warm->value);
        $this->assertSame('reply', ContactStage::Reply->value);
    }

    public function test_tier_config_defaults_are_unlimited(): void
    {
        $tier = new TierConfig;

        $this->assertSame(0, $tier->maxMessagesPerWindow);
        $this->assertSame(0, $tier->sessionMaxMessages);
        $this->assertNull($tier->sessionMaxUniqueTargets);
        $this->assertSame(0, $tier->cooldownMinMs);
        $this->assertSame(0, $tier->cooldownMaxMs);
    }

    public function test_tier_config_from_array(): void
    {
        $tier = TierConfig::fromArray([
            'max_messages_per_window'    => 1,
            'window_seconds'             => 86400,
            'session_max_messages'       => 15,
            'session_window_seconds'     => 86400,
            'session_max_unique_targets' => 10,
            'cooldown_min_ms'            => 60000,
            'cooldown_max_ms'            => 180000,
        ]);

        $this->assertSame(1, $tier->maxMessagesPerWindow);
        $this->assertSame(86400, $tier->windowSeconds);
        $this->assertSame(15, $tier->sessionMaxMessages);
        $this->assertSame(10, $tier->sessionMaxUniqueTargets);
        $this->assertSame(60000, $tier->cooldownMinMs);
        $this->assertSame(180000, $tier->cooldownMaxMs);
    }

    public function test_tier_config_rejects_inverted_cooldown(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TierConfig(cooldownMinMs: 100, cooldownMaxMs: 50);
    }

    public function test_pacing_off_zeroes_mechanics_and_tiers(): void
    {
        $policy = Pacing::off();

        $this->assertFalse($policy->humanize);

        foreach (ContactStage::cases() as $stage) {
            $tier = $policy->tier($stage);

            $this->assertSame(0, $tier->maxMessagesPerWindow);
            $this->assertSame(0, $tier->sessionMaxMessages);
            $this->assertNull($tier->sessionMaxUniqueTargets);
        }
    }

    public function test_pacing_default_exposes_conservative_tiers(): void
    {
        $policy = Pacing::default();

        $this->assertSame(1, $policy->tier(ContactStage::Cold)->maxMessagesPerWindow);
        $this->assertSame(5, $policy->tier(ContactStage::Warm)->maxMessagesPerWindow);
        $this->assertSame(20, $policy->tier(ContactStage::Reply)->maxMessagesPerWindow);
        $this->assertSame(10, $policy->tier(ContactStage::Cold)->sessionMaxUniqueTargets);
        $this->assertNull($policy->tier(ContactStage::Warm)->sessionMaxUniqueTargets);
    }

    public function test_spintax_parse_resolves_to_one_variant(): void
    {
        $result = Spintax::parse('Hello {friend|there}');

        $this->assertContains($result, ['Hello friend', 'Hello there']);
    }

    public function test_spintax_count_is_product_of_branches(): void
    {
        $this->assertSame(2, Spintax::count('{a|b}'));
        $this->assertSame(4, Spintax::count('{a|b}{c|d}'));
        $this->assertSame(3, Spintax::count('{a|{b|c}}'));
    }

    public function test_spintax_validate_rejects_unbalanced_and_empty_branches(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Spintax::validate('{a|b');
    }

    public function test_spintax_validate_rejects_empty_branch(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Spintax::validate('{a|}');
    }
}
