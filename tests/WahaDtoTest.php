<?php

declare(strict_types=1);

namespace DenLopes\Waha\Tests;

use DenLopes\Waha\Data\Input\ApiKeyRequest;
use DenLopes\Waha\Data\Input\Button;
use DenLopes\Waha\Data\Output\MessageCapping;
use DenLopes\Waha\Data\Output\MessageData;
use DenLopes\Waha\Data\Output\Ping;
use DenLopes\Waha\Data\Output\SessionInfo;
use DenLopes\Waha\Data\Output\Webhook;
use DenLopes\Waha\Enums\AckCode;
use DenLopes\Waha\Enums\ButtonType;
use DenLopes\Waha\Enums\MessageCappingStatus;
use DenLopes\Waha\Enums\MessageSource;
use DenLopes\Waha\Enums\SessionStatus;
use DenLopes\Waha\Exceptions\IntegrationException;
use DenLopes\Waha\Session;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class WahaDtoTest extends TestCase
{
    public function test_message_dto_casts_scalars_and_source_enum(): void
    {
        $dto = MessageData::fromArray([
            'id'        => 'false_11111111111@c.us_ABC',
            'timestamp' => '1666943582',
            'fromMe'    => 1,
            'hasMedia'  => 0,
            'body'      => 12345,
            'source'    => 'api',
            'ack'       => '2',
        ]);

        $this->assertSame('false_11111111111@c.us_ABC', $dto->id);
        $this->assertSame(1666943582, $dto->timestamp);
        $this->assertTrue($dto->fromMe);
        $this->assertFalse($dto->hasMedia);
        $this->assertSame('12345', $dto->body);
        $this->assertSame(MessageSource::API, $dto->source);
        $this->assertSame(AckCode::DEVICE, $dto->ack);
    }

    public function test_message_dto_tolerates_unknown_source(): void
    {
        $dto = MessageData::fromArray(['id' => 'x', 'source' => 'future-device']);

        $this->assertNull($dto->source);
    }

    public function test_capping_status_enum_and_raw_preserved(): void
    {
        $dto = MessageCapping::fromArray([
            'cappingStatus' => 'CAPPED',
            'totalQuota'    => '1000',
            'usedQuota'     => 640,
        ]);

        $this->assertSame(MessageCappingStatus::CAPPED, $dto->cappingStatus);
        $this->assertSame('CAPPED', $dto->cappingStatusRaw);
        $this->assertSame(1000, $dto->totalQuota);
        $this->assertSame(640, $dto->usedQuota);
    }

    public function test_button_type_enum_serializes_to_value(): void
    {
        $button = new Button('Click');

        $this->assertSame(ButtonType::REPLY, $button->type);
        $this->assertSame('reply', $button->toArray()['type']);

        $urlButton = Button::fromArray(['text' => 'Open', 'type' => 'url']);
        $this->assertSame(ButtonType::URL, $urlButton->type);
    }

    public function test_session_info_status_enum(): void
    {
        $dto = SessionInfo::fromArray(['name' => 'default', 'status' => 'WORKING']);

        $this->assertSame(SessionStatus::WORKING, $dto->status);
    }

    public function test_webhook_payload_maps_to_typed_dto(): void
    {
        $dto = Webhook::fromArray([
            'event'   => 'message',
            'payload' => [
                'id'     => 'false_11111111111@c.us_ABC',
                'body'   => 'Hello',
                'fromMe' => true,
            ],
        ]);

        $this->assertInstanceOf(MessageData::class, $dto->payload);
        $this->assertSame('Hello', $dto->payload->body);
    }

    public function test_to_array_skips_null_and_serializes_enum(): void
    {
        $dto = new ApiKeyRequest(isAdmin: false, isActive: true, session: null);

        $this->assertSame(['isAdmin' => false, 'isActive' => true], $dto->toArray());
    }

    public function test_session_value_object_normalizes_and_validates(): void
    {
        $this->assertSame('default', (new Session(' default '))->value());

        $this->expectException(InvalidArgumentException::class);
        new Session('   ');
    }

    public function test_from_json_wraps_invalid_json_in_domain_exception(): void
    {
        $this->expectException(IntegrationException::class);

        Ping::fromJson('{invalid');
    }
}
