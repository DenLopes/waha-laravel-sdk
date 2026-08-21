<?php

declare(strict_types=1);

namespace DenLopes\Waha\Tests;

use DenLopes\Waha\Data\Output\ChatPresences;
use DenLopes\Waha\Data\Output\EnginePayload;
use DenLopes\Waha\Data\Output\EventResponsePayload;
use DenLopes\Waha\Data\Output\GroupV2JoinEvent;
use DenLopes\Waha\Data\Output\Label;
use DenLopes\Waha\Data\Output\MeInfo;
use DenLopes\Waha\Data\Output\MessageAckBody;
use DenLopes\Waha\Data\Output\MessageCapping;
use DenLopes\Waha\Data\Output\MessageData;
use DenLopes\Waha\Data\Output\MessageEditedBody;
use DenLopes\Waha\Data\Output\MessageReaction;
use DenLopes\Waha\Data\Output\MessageRevokedBody;
use DenLopes\Waha\Data\Output\PollVotePayload;
use DenLopes\Waha\Data\Output\ReachoutTimelock;
use DenLopes\Waha\Data\Output\SessionStatusBody;
use DenLopes\Waha\Data\Output\Webhook;
use PHPUnit\Framework\TestCase;

final class WahaWebhookParsingTest extends TestCase
{
    public function test_message_events_map_to_message_data(): void
    {
        $this->assertInstanceOf(MessageData::class, $this->payload('message'));
        $this->assertInstanceOf(MessageData::class, $this->payload('message.any'));
    }

    public function test_ack_events_map_to_ack_body(): void
    {
        $this->assertInstanceOf(MessageAckBody::class, $this->payload('message.ack'));
        $this->assertInstanceOf(MessageAckBody::class, $this->payload('message.ack.group'));
    }

    public function test_reaction_revoked_and_edited_events_map_to_their_dtos(): void
    {
        $this->assertInstanceOf(MessageReaction::class, $this->payload('message.reaction'));
        $this->assertInstanceOf(MessageRevokedBody::class, $this->payload('message.revoked'));
        $this->assertInstanceOf(MessageEditedBody::class, $this->payload('message.edited'));
    }

    public function test_session_status_maps_to_status_body(): void
    {
        $this->assertInstanceOf(SessionStatusBody::class, $this->payload('session.status'));
    }

    public function test_presence_and_poll_vote_events_map_to_their_dtos(): void
    {
        $this->assertInstanceOf(ChatPresences::class, $this->payload('presence.update'));
        $this->assertInstanceOf(PollVotePayload::class, $this->payload('poll.vote'));
    }

    public function test_group_label_and_engine_events_map_to_their_dtos(): void
    {
        $this->assertInstanceOf(GroupV2JoinEvent::class, $this->payload('group.v2.join'));
        $this->assertInstanceOf(Label::class, $this->payload('label.upsert'));
        $this->assertInstanceOf(EventResponsePayload::class, $this->payload('event.response'));
        $this->assertInstanceOf(EnginePayload::class, $this->payload('engine.event'));
    }

    public function test_unknown_event_keeps_raw_payload(): void
    {
        $webhook = Webhook::fromArray([
            'event'   => 'something.new',
            'payload' => ['foo' => 'bar'],
        ]);

        $this->assertSame(['foo' => 'bar'], $webhook->payload);
    }

    public function test_me_info_maps_nested_reachout_and_capping(): void
    {
        $webhook = Webhook::fromArray([
            'event' => 'message',
            'me'    => [
                'id'               => 'bot@c.us',
                'reachoutTimelock' => ['isActive' => true],
                'messageCapping'   => ['totalQuota' => 1000, 'usedQuota' => 10],
            ],
            'payload' => ['id' => 'x'],
        ]);

        $this->assertInstanceOf(MeInfo::class, $webhook->me);
        $this->assertInstanceOf(ReachoutTimelock::class, $webhook->me->reachoutTimelock);
        $this->assertTrue($webhook->me->reachoutTimelock->isActive);
        $this->assertInstanceOf(MessageCapping::class, $webhook->me->messageCapping);
        $this->assertSame(1000, $webhook->me->messageCapping->totalQuota);
    }

    private function payload(string $event): mixed
    {
        return Webhook::fromArray([
            'event'   => $event,
            'payload' => ['id' => 'false_11111111111@c.us_ABC'],
        ])->payload;
    }
}
