<?php

declare(strict_types=1);

namespace DenLopes\Waha\Tests;

use DenLopes\Waha\Data\Output\ContactInfoData;
use DenLopes\Waha\Data\Output\GroupJoinInfoData;
use DenLopes\Waha\Data\Output\SessionInfoData;
use DenLopes\Waha\Data\Output\WAMessageData;
use DenLopes\Waha\Services\ChattingService;
use DenLopes\Waha\Services\ContactsService;
use DenLopes\Waha\Services\GroupsService;
use DenLopes\Waha\Services\SessionService;
use DenLopes\Waha\Support\WahaSession;
use DenLopes\Waha\Tests\Support\FakeWahaClient;
use PHPUnit\Framework\TestCase;

final class WahaServiceTest extends TestCase
{
    public function test_list_sessions_returns_typed_dtos(): void
    {
        $fake = new FakeWahaClient([
            ['name' => 'default', 'status' => 'WORKING'],
            ['name' => 'sales', 'status' => 'STOPPED'],
        ]);

        $sessions = (new SessionService($fake))->listSessions();

        $this->assertCount(2, $sessions);
        $this->assertInstanceOf(SessionInfoData::class, $sessions[0]);
        $this->assertSame('default', $sessions[0]->name);
        $this->assertSame('/api/sessions', $fake->requests[0]['endpoint']);
        $this->assertSame('get', $fake->requests[0]['method']);
    }

    public function test_get_session_uses_expected_endpoint(): void
    {
        $fake = new FakeWahaClient(['name' => 'default', 'status' => 'WORKING']);

        $session = (new SessionService($fake))->getSession(WahaSession::from('default'));

        $this->assertInstanceOf(SessionInfoData::class, $session);
        $this->assertSame('/api/sessions/default', $fake->requests[0]['endpoint']);
    }

    public function test_send_text_builds_payload_and_returns_message(): void
    {
        $fake = new FakeWahaClient([
            'id'     => 'false_11111111111@c.us_ABC',
            'fromMe' => true,
            'body'   => 'Hello',
        ]);

        $message = (new ChattingService($fake))->sendText(
            '11111111111@c.us',
            'Hello',
            WahaSession::from('default'),
        );

        $this->assertInstanceOf(WAMessageData::class, $message);

        $payload = $fake->requests[0]['payload'];
        $this->assertSame('11111111111@c.us', $payload['chatId']);
        $this->assertSame('Hello', $payload['text']);
        $this->assertSame('default', $payload['session']);
        $this->assertSame('default', $fake->requests[0]['session']);
    }

    public function test_get_all_contacts_returns_typed_dtos(): void
    {
        $fake = new FakeWahaClient([
            ['id' => '11111111111@c.us', 'name' => 'John', 'pushName' => 'Johnny'],
        ]);

        $contacts = (new ContactsService($fake))->getAllContacts(WahaSession::from('default'));

        $this->assertCount(1, $contacts);
        $this->assertInstanceOf(ContactInfoData::class, $contacts[0]);
        $this->assertSame('John', $contacts[0]->name);
        $this->assertSame('/api/contacts/all', $fake->requests[0]['endpoint']);
    }

    public function test_get_join_info_returns_typed_dto(): void
    {
        $fake = new FakeWahaClient([
            'id'      => '123123123@g.us',
            'subject' => 'Group',
        ]);

        $info = (new GroupsService($fake))->getJoinInfo(WahaSession::from('default'), 'code');

        $this->assertInstanceOf(GroupJoinInfoData::class, $info);
        $this->assertSame('Group', $info->subject);
        $this->assertSame('/api/default/groups/join-info', $fake->requests[0]['endpoint']);
        $this->assertSame('code', $fake->requests[0]['payload']['code']);
    }
}
