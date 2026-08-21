<?php

declare(strict_types=1);

namespace DenLopes\Waha\Tests;

use DenLopes\Waha\Data\App;
use DenLopes\Waha\Data\Input\EventMessage;
use DenLopes\Waha\Data\Input\RejectCallRequest;
use DenLopes\Waha\Data\Input\VoiceFile;
use DenLopes\Waha\Data\Output\ApiKey;
use DenLopes\Waha\Data\Output\Base64File;
use DenLopes\Waha\Data\Output\Channel;
use DenLopes\Waha\Data\Output\ChatData;
use DenLopes\Waha\Data\Output\CountResponse;
use DenLopes\Waha\Data\Output\Label;
use DenLopes\Waha\Data\Output\MessageData;
use DenLopes\Waha\Data\Output\MyProfile;
use DenLopes\Waha\Data\Output\NewMessageId;
use DenLopes\Waha\Data\Output\Ping;
use DenLopes\Waha\Data\Output\QRCodeValue;
use DenLopes\Waha\Enums\PresenceStatus;
use DenLopes\Waha\Enums\QrFormat;
use DenLopes\Waha\Services\ApiKeysService;
use DenLopes\Waha\Services\AppsService;
use DenLopes\Waha\Services\CallsService;
use DenLopes\Waha\Services\ChannelsService;
use DenLopes\Waha\Services\ChatsService;
use DenLopes\Waha\Services\EventsService;
use DenLopes\Waha\Services\LabelsService;
use DenLopes\Waha\Services\LidsService;
use DenLopes\Waha\Services\MediaService;
use DenLopes\Waha\Services\ObservabilityService;
use DenLopes\Waha\Services\PairingService;
use DenLopes\Waha\Services\PresenceService;
use DenLopes\Waha\Services\ProfileService;
use DenLopes\Waha\Services\StatusService;
use DenLopes\Waha\Session;
use DenLopes\Waha\Tests\Support\FakeWahaClient;
use PHPUnit\Framework\TestCase;

final class WahaServicesCoverageTest extends TestCase
{
    public function test_api_keys_service_lists_keys(): void
    {
        $fake = new FakeWahaClient([[]]);

        $keys = (new ApiKeysService($fake))->listApiKeys();

        $this->assertCount(1, $keys);
        $this->assertInstanceOf(ApiKey::class, $keys[0]);
        $this->assertSame('get', $fake->requests[0]['method']);
        $this->assertSame('/api/keys', $fake->requests[0]['endpoint']);
    }

    public function test_apps_service_lists_apps(): void
    {
        $fake = new FakeWahaClient([[]]);

        $apps = (new AppsService($fake))->listApps(Session::from('default'));

        $this->assertCount(1, $apps);
        $this->assertInstanceOf(App::class, $apps[0]);
        $this->assertSame('/api/apps', $fake->requests[0]['endpoint']);
    }

    public function test_calls_service_rejects_a_call(): void
    {
        $fake = new FakeWahaClient([]);

        (new CallsService($fake))->rejectCall(Session::from('default'), RejectCallRequest::fromArray([]));

        $this->assertSame('post', $fake->requests[0]['method']);
        $this->assertSame('/api/default/calls/reject', $fake->requests[0]['endpoint']);
    }

    public function test_channels_service_lists_channels(): void
    {
        $fake = new FakeWahaClient([[]]);

        $channels = (new ChannelsService($fake))->listChannels(Session::from('default'));

        $this->assertCount(1, $channels);
        $this->assertInstanceOf(Channel::class, $channels[0]);
        $this->assertSame('/api/default/channels', $fake->requests[0]['endpoint']);
    }

    public function test_chats_service_builds_filter_payload(): void
    {
        $fake = new FakeWahaClient([[]]);

        (new ChatsService($fake))->getChats(Session::from('default'), limit: 5, offset: 10);

        $this->assertSame('/api/default/chats', $fake->requests[0]['endpoint']);
        $this->assertSame(5, $fake->requests[0]['payload']['limit']);
        $this->assertSame(10, $fake->requests[0]['payload']['offset']);
    }

    public function test_chats_service_returns_typed_messages(): void
    {
        $fake = new FakeWahaClient(['id' => 'false_11111111111@c.us_ABC']);

        $message = (new ChatsService($fake))->getChatMessage(Session::from('default'), '11111111111@c.us', 'ABC');

        $this->assertInstanceOf(MessageData::class, $message);
        $this->assertSame('/api/default/chats/11111111111@c.us/messages/ABC', $fake->requests[0]['endpoint']);
    }

    public function test_chats_service_lists_chats(): void
    {
        $fake = new FakeWahaClient([[]]);

        $chats = (new ChatsService($fake))->getChats(Session::from('default'));

        $this->assertCount(1, $chats);
        $this->assertInstanceOf(ChatData::class, $chats[0]);
    }

    public function test_events_service_sends_event(): void
    {
        $fake = new FakeWahaClient(['id' => 'false_11111111111@c.us_ABC']);

        $message = (new EventsService($fake))->sendEvent(Session::from('default'), '11111111111@c.us', EventMessage::fromArray([]));

        $this->assertInstanceOf(MessageData::class, $message);
        $this->assertSame('post', $fake->requests[0]['method']);
        $this->assertSame('/api/default/events', $fake->requests[0]['endpoint']);
    }

    public function test_labels_service_lists_labels(): void
    {
        $fake = new FakeWahaClient([[]]);

        $labels = (new LabelsService($fake))->getLabels(Session::from('default'));

        $this->assertCount(1, $labels);
        $this->assertInstanceOf(Label::class, $labels[0]);
        $this->assertSame('/api/default/labels', $fake->requests[0]['endpoint']);
    }

    public function test_lids_service_counts_lids(): void
    {
        $fake = new FakeWahaClient([]);

        $count = (new LidsService($fake))->getCount(Session::from('default'));

        $this->assertInstanceOf(CountResponse::class, $count);
        $this->assertSame('/api/default/lids/count', $fake->requests[0]['endpoint']);
    }

    public function test_media_service_converts_voice_to_base64(): void
    {
        $fake = new FakeWahaClient([], '');
        $fake->response = ['base64' => 'AAAA'];

        $result = (new MediaService($fake))->convertVoice(Session::from('default'), VoiceFile::fromArray([]), asBase64: true);

        $this->assertInstanceOf(Base64File::class, $result);
        $this->assertSame('/api/default/media/convert/voice', $fake->requests[0]['endpoint']);
    }

    public function test_observability_service_pings_unauthenticated(): void
    {
        $fake = new FakeWahaClient([]);

        $ping = (new ObservabilityService($fake))->ping();

        $this->assertInstanceOf(Ping::class, $ping);
        $this->assertSame('get', $fake->requests[0]['method']);
        $this->assertSame('/ping', $fake->requests[0]['endpoint']);
        $this->assertFalse($fake->requests[0]['authenticated']);
    }

    public function test_pairing_service_fetches_raw_qr_value(): void
    {
        $fake = new FakeWahaClient(['code' => 'ABC']);

        $qr = (new PairingService($fake))->getQrCode(Session::from('default'), QrFormat::RAW);

        $this->assertInstanceOf(QRCodeValue::class, $qr);
        $this->assertSame('/api/default/auth/qr', $fake->requests[0]['endpoint']);
    }

    public function test_presence_service_sets_presence(): void
    {
        $fake = new FakeWahaClient([]);

        (new PresenceService($fake))->setPresence(PresenceStatus::ONLINE, session: Session::from('default'));

        $this->assertSame('post', $fake->requests[0]['method']);
        $this->assertSame('/api/default/presence', $fake->requests[0]['endpoint']);
        $this->assertSame('online', $fake->requests[0]['payload']['presence']);
    }

    public function test_profile_service_fetches_my_profile(): void
    {
        $fake = new FakeWahaClient([]);

        $profile = (new ProfileService($fake))->getMyProfile(Session::from('default'));

        $this->assertInstanceOf(MyProfile::class, $profile);
        $this->assertSame('/api/default/profile', $fake->requests[0]['endpoint']);
    }

    public function test_status_service_generates_new_message_id(): void
    {
        $fake = new FakeWahaClient([]);

        $id = (new StatusService($fake))->getNewStatusMessageId(Session::from('default'));

        $this->assertInstanceOf(NewMessageId::class, $id);
        $this->assertSame('/api/default/status/new-message-id', $fake->requests[0]['endpoint']);
    }
}
