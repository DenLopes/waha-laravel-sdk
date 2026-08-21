<?php

declare(strict_types=1);

namespace DenLopes\Waha\Tests;

use DenLopes\Waha\Client;
use DenLopes\Waha\Data\Input\LinkPreview;
use DenLopes\Waha\Data\Input\RemoteFile;
use DenLopes\Waha\Data\Input\SendListMessage;
use DenLopes\Waha\Resources\Chat;
use DenLopes\Waha\Resources\Message;
use DenLopes\Waha\Services\ChatsService;
use DenLopes\Waha\Services\MessagingService;
use DenLopes\Waha\Session;
use DenLopes\Waha\Support\ArrayColdTargetLimiter;
use DenLopes\Waha\Support\ArraySessionRateLimiter;
use DenLopes\Waha\Support\ConversationFactory;
use DenLopes\Waha\Tests\Support\FakeCircuitBreaker;
use DenLopes\Waha\Tests\Support\FakeContactStageStore;
use DenLopes\Waha\Tests\Support\FakeConversationStateStore;
use DenLopes\Waha\Tests\Support\FakeReachoutGuard;
use DenLopes\Waha\Tests\Support\FakeWahaClient;
use DenLopes\Waha\Tests\Support\FakeWarmupTracker;
use PHPUnit\Framework\TestCase;

final class WahaFluentTest extends TestCase
{
    public function test_send_message_returns_fluent_handle(): void
    {
        [$chat] = $this->makeChat();

        $message = $chat->sendMessage('Hello');

        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame('false_11111111111@c.us_ABC', $message->id());
    }

    public function test_send_image_returns_fluent_handle(): void
    {
        [$chat, $fake] = $this->makeChat();

        $message = $chat->sendImage(new RemoteFile('image/jpeg', 'https://example.com/pic.jpg'));

        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame('false_11111111111@c.us_ABC', $message->id());
        $this->assertSame('/api/sendImage', $fake->requests[0]['endpoint']);
    }

    public function test_chat_actions_are_chainable(): void
    {
        [$chat] = $this->makeChat();

        $result = $chat->startTyping()->markRead()->archive();

        $this->assertSame($chat, $result);
    }

    public function test_message_can_be_pinned_unpinned_and_forwarded(): void
    {
        $fake = new FakeWahaClient([
            'id'     => 'false_22222222222@c.us_ABC',
            'fromMe' => true,
        ]);

        $message = new Message(
            Session::from('default'),
            '11111111111@c.us',
            'false_11111111111@c.us_ABC',
            new ChatsService($fake),
            new MessagingService($fake),
        );

        $this->assertSame($message, $message->pin(60));
        $this->assertSame($message, $message->unpin());

        $forwarded = $message->forward('22222222222@c.us');

        $this->assertInstanceOf(Message::class, $forwarded);
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

        $message = $chat->sendList(new SendListMessage('Menu', 'Select', []));

        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame('/api/sendList', $fake->requests[0]['endpoint']);
    }

    public function test_send_link_custom_preview_returns_fluent_handle(): void
    {
        [$chat, $fake] = $this->makeChat();

        $message = $chat->sendLinkCustomPreview(
            'Check this out',
            new LinkPreview('https://github.com/', 'GitHub', 'Where the world builds software'),
        );

        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame('/api/send/link-custom-preview', $fake->requests[0]['endpoint']);
    }

    public function test_client_accepts_string_or_value_object_session(): void
    {
        $fake = new FakeWahaClient;

        $manager = new Client(
            new MessagingService($fake),
            new ChatsService($fake),
            $this->makeConversationFactory(),
        );

        $this->assertSame('sales', $manager->chat('11111111111@c.us', 'sales')->session()->value());
        $this->assertSame('sales', $manager->chat('11111111111@c.us', Session::from('sales'))->session()->value());
        $this->assertSame('sales', $manager->session('sales')->value());
    }

    /**
     * @return array{Chat, FakeWahaClient}
     */
    private function makeChat(): array
    {
        $fake = new FakeWahaClient([
            'id'     => 'false_11111111111@c.us_ABC',
            'fromMe' => true,
            'body'   => 'Hello',
        ]);

        $chat = new Chat(
            Session::from('default'),
            '11111111111@c.us',
            new MessagingService($fake),
            new ChatsService($fake),
            $this->makeConversationFactory(),
        );

        return [$chat, $fake];
    }

    private function makeConversationFactory(): ConversationFactory
    {
        return new ConversationFactory(
            stateStore: new FakeConversationStateStore,
            contactStageStore: new FakeContactStageStore,
            sessionLimiter: new ArraySessionRateLimiter,
            coldTargetLimiter: new ArrayColdTargetLimiter,
            reachoutGuard: new FakeReachoutGuard,
            warmupTracker: new FakeWarmupTracker,
            circuitBreaker: new FakeCircuitBreaker,
        );
    }
}
