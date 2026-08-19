<?php

declare(strict_types=1);

namespace DenLopes\Waha\Tests;

use DenLopes\Waha\Contracts\PinStore;
use DenLopes\Waha\Data\Input\LinkPreviewData;
use DenLopes\Waha\Data\Input\RemoteFileData;
use DenLopes\Waha\Data\Input\SendListMessageData;
use DenLopes\Waha\Debug\WahaDebugStore;
use DenLopes\Waha\Fluent\WahaChat;
use DenLopes\Waha\Fluent\WahaManager;
use DenLopes\Waha\Fluent\WahaMessage;
use DenLopes\Waha\Services\ChatsService;
use DenLopes\Waha\Services\ChattingService;
use DenLopes\Waha\Support\WahaSession;
use DenLopes\Waha\Tests\Support\FakeWahaClient;
use PHPUnit\Framework\TestCase;

final class WahaFluentTest extends TestCase
{
    public function test_send_message_returns_fluent_handle(): void
    {
        [$chat] = $this->makeChat();

        $message = $chat->sendMessage('Hello');

        $this->assertInstanceOf(WahaMessage::class, $message);
        $this->assertSame('false_11111111111@c.us_ABC', $message->id());
    }

    public function test_send_image_returns_fluent_handle(): void
    {
        [$chat, $fake] = $this->makeChat();

        $message = $chat->sendImage(new RemoteFileData('image/jpeg', 'https://example.com/pic.jpg'));

        $this->assertInstanceOf(WahaMessage::class, $message);
        $this->assertSame('false_11111111111@c.us_ABC', $message->id());
        $this->assertSame('/api/sendImage', $fake->requests[0]['endpoint']);
    }

    public function test_chat_actions_are_chainable(): void
    {
        [$chat] = $this->makeChat();

        $result = $chat->startTyping()->sendSeen()->archive();

        $this->assertSame($chat, $result);
    }

    public function test_message_can_be_pinned_unpinned_and_forwarded(): void
    {
        $fake = new FakeWahaClient([
            'id'     => 'false_22222222222@c.us_ABC',
            'fromMe' => true,
        ]);

        $message = new WahaMessage(
            WahaSession::from('default'),
            '11111111111@c.us',
            'false_11111111111@c.us_ABC',
            new ChatsService($fake),
            new ChattingService($fake),
        );

        $this->assertSame($message, $message->pin(60));
        $this->assertSame($message, $message->unpin());

        $forwarded = $message->forward('22222222222@c.us');

        $this->assertInstanceOf(WahaMessage::class, $forwarded);
        $this->assertSame('22222222222@c.us', $forwarded->chatId());
        $this->assertSame('false_22222222222@c.us_ABC', $forwarded->id());
    }

    public function test_message_handle_is_lazy_until_get(): void
    {
        [$chat, $fake] = $this->makeChat();

        $message = $chat->message('false_11111111111@c.us_ABC');

        $this->assertCount(0, $fake->requests);

        $message->get();

        $this->assertCount(1, $fake->requests);
    }

    public function test_message_to_array_and_json_use_snapshot(): void
    {
        [$chat, $fake] = $this->makeChat();

        $message = $chat->sendMessage('Hello');

        $this->assertSame('false_11111111111@c.us_ABC', $message->toArray()['id']);
        $this->assertSame('Hello', $message->toArray()['body']);

        $decoded = json_decode($message->toJson(), true);
        $this->assertSame('false_11111111111@c.us_ABC', $decoded['id']);

        // Both accessors reuse the snapshot without extra network calls.
        $this->assertCount(1, $fake->requests);
    }

    public function test_send_list_returns_fluent_handle(): void
    {
        [$chat, $fake] = $this->makeChat();

        $message = $chat->sendList(new SendListMessageData('Menu', 'Select', []));

        $this->assertInstanceOf(WahaMessage::class, $message);
        $this->assertSame('/api/sendList', $fake->requests[0]['endpoint']);
    }

    public function test_send_link_custom_preview_returns_fluent_handle(): void
    {
        [$chat, $fake] = $this->makeChat();

        $message = $chat->sendLinkCustomPreview(
            'Check this out',
            new LinkPreviewData('https://github.com/', 'GitHub', 'Where the world builds software'),
        );

        $this->assertInstanceOf(WahaMessage::class, $message);
        $this->assertSame('/api/send/link-custom-preview', $fake->requests[0]['endpoint']);
    }

    public function test_manager_accepts_string_or_value_object_session(): void
    {
        $fake = new FakeWahaClient;
        $pins = new class implements PinStore
        {
            public function getHostForSession(string $sessionName): ?string
            {
                return null;
            }

            public function pin(string $sessionName, string $hostKey, ?int $ttlSeconds = null): void {}

            public function forget(string $sessionName): void {}
        };

        $manager = new WahaManager(
            new ChattingService($fake),
            new ChatsService($fake),
            $pins,
            new WahaDebugStore,
        );

        $this->assertSame('sales', $manager->chat('11111111111@c.us', 'sales')->session()->value());
        $this->assertSame('sales', $manager->chat('11111111111@c.us', WahaSession::from('sales'))->session()->value());
        $this->assertSame('sales', $manager->session('sales')->value());
    }

    /**
     * @return array{WahaChat, FakeWahaClient}
     */
    private function makeChat(): array
    {
        $fake = new FakeWahaClient([
            'id'     => 'false_11111111111@c.us_ABC',
            'fromMe' => true,
            'body'   => 'Hello',
        ]);

        $chat = new WahaChat(
            WahaSession::from('default'),
            '11111111111@c.us',
            new ChattingService($fake),
            new ChatsService($fake),
        );

        return [$chat, $fake];
    }
}
