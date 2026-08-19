<?php

declare(strict_types=1);

namespace DenLopes\Waha\Tests;

use DenLopes\Waha\Data\Input\ApiKeyRequestData;
use DenLopes\Waha\Data\Input\ButtonData;
use DenLopes\Waha\Data\Output\MessageCappingData;
use DenLopes\Waha\Data\Output\PingData;
use DenLopes\Waha\Data\Output\SessionInfoData;
use DenLopes\Waha\Data\Output\WAMessageData;
use DenLopes\Waha\Data\Output\WebhookData;
use DenLopes\Waha\Enums\WahaButtonTypeEnum;
use DenLopes\Waha\Enums\WahaMessageCappingStatusEnum;
use DenLopes\Waha\Enums\WahaMessageSourceEnum;
use DenLopes\Waha\Enums\WahaSessionStatusEnum;
use DenLopes\Waha\Exception\WahaIntegrationException;
use DenLopes\Waha\Support\WahaSession;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class WahaDtoTest extends TestCase
{
    public function test_message_dto_casts_scalars_and_source_enum(): void
    {
        $dto = WAMessageData::fromArray([
            'id' => 'false_11111111111@c.us_ABC',
            'timestamp' => '1666943582',
            'fromMe' => 1,
            'hasMedia' => 0,
            'body' => 12345,
            'source' => 'api',
            'ack' => '2',
        ]);

        $this->assertSame('false_11111111111@c.us_ABC', $dto->id);
        $this->assertSame(1666943582, $dto->timestamp);
        $this->assertTrue($dto->fromMe);
        $this->assertFalse($dto->hasMedia);
        $this->assertSame('12345', $dto->body);
        $this->assertSame(WahaMessageSourceEnum::API, $dto->source);
        $this->assertSame(2, $dto->ack);
    }

    public function test_message_dto_tolerates_unknown_source(): void
    {
        $dto = WAMessageData::fromArray(['id' => 'x', 'source' => 'future-device']);

        $this->assertNull($dto->source);
    }

    public function test_capping_status_enum_and_raw_preserved(): void
    {
        $dto = MessageCappingData::fromArray([
            'cappingStatus' => 'CAPPED',
            'totalQuota' => '1000',
            'usedQuota' => 640,
        ]);

        $this->assertSame(WahaMessageCappingStatusEnum::CAPPED, $dto->cappingStatus);
        $this->assertSame('CAPPED', $dto->cappingStatusRaw);
        $this->assertSame(1000, $dto->totalQuota);
        $this->assertSame(640, $dto->usedQuota);
    }

    public function test_button_type_enum_serializes_to_value(): void
    {
        $button = new ButtonData('Click');

        $this->assertSame(WahaButtonTypeEnum::REPLY, $button->type);
        $this->assertSame('reply', $button->toArray()['type']);

        $urlButton = ButtonData::fromArray(['text' => 'Open', 'type' => 'url']);
        $this->assertSame(WahaButtonTypeEnum::URL, $urlButton->type);
    }

    public function test_session_info_status_enum(): void
    {
        $dto = SessionInfoData::fromArray(['name' => 'default', 'status' => 'WORKING']);

        $this->assertSame(WahaSessionStatusEnum::WORKING, $dto->status);
    }

    public function test_webhook_payload_maps_to_typed_dto(): void
    {
        $dto = WebhookData::fromArray([
            'event' => 'message',
            'payload' => [
                'id' => 'false_11111111111@c.us_ABC',
                'body' => 'Hello',
                'fromMe' => true,
            ],
        ]);

        $this->assertInstanceOf(WAMessageData::class, $dto->payload);
        $this->assertSame('Hello', $dto->payload->body);
    }

    public function test_to_array_skips_null_and_serializes_enum(): void
    {
        $dto = new ApiKeyRequestData(isAdmin: false, isActive: true, session: null);

        $this->assertSame(['isAdmin' => false, 'isActive' => true], $dto->toArray());
    }

    public function test_session_value_object_normalizes_and_validates(): void
    {
        $this->assertSame('default', (new WahaSession(' default '))->value());

        $this->expectException(InvalidArgumentException::class);
        new WahaSession('   ');
    }

    public function test_from_json_wraps_invalid_json_in_domain_exception(): void
    {
        $this->expectException(WahaIntegrationException::class);

        PingData::fromJson('{invalid');
    }
}
