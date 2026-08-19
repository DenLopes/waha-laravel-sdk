<?php

declare(strict_types=1);

namespace DenLopes\Waha\Tests;

use DenLopes\Waha\Client;
use DenLopes\Waha\Data\Input\LinkPreview;
use DenLopes\Waha\Data\Input\RemoteFile;
use DenLopes\Waha\Data\Input\SendListMessage;
use DenLopes\Waha\Exceptions\ConversationThrottledException;
use DenLopes\Waha\Resources\Chat;
use DenLopes\Waha\Resources\Conversation;
use DenLopes\Waha\Resources\Message;
use DenLopes\Waha\Services\ChatsService;
use DenLopes\Waha\Services\MessagingService;
use DenLopes\Waha\Session;
use DenLopes\Waha\Support\Pacing;
use DenLopes\Waha\Tests\Support\FakeWahaClient;
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
        );

        $this->assertSame('sales', $manager->chat('11111111111@c.us', 'sales')->session()->value());
        $this->assertSame('sales', $manager->chat('11111111111@c.us', Session::from('sales'))->session()->value());
        $this->assertSame('sales', $manager->session('sales')->value());
    }

    public function test_conversation_send_follows_anti_ban_flow(): void
    {
        [$chat, $fake] = $this->makeChat();

        $sleeps = [];
        $conversation = new Conversation(
            $chat,
            new Pacing(
                humanize: true,
                minTypingMs: 0,
                maxTypingMs: 0,
                typingMsPerCharacter: 0.0,
                cooldownMinMs: 0,
                cooldownMaxMs: 0,
                maxMessagesPerWindow: 0,
            ),
            static function (int $ms) use (&$sleeps): void {
                $sleeps[] = $ms;
            },
        );

        $message = $conversation->send('Hello');

        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame([
            '/api/sendSeen',
            '/api/startTyping',
            '/api/stopTyping',
            '/api/sendText',
        ], array_column($fake->requests, 'endpoint'));
        $this->assertSame([], $sleeps);
    }

    public function test_conversation_pauses_after_spaces_when_roll_succeeds(): void
    {
        [$chat, $fake] = $this->makeChat();

        $sleeps = [];
        $conversation = new Conversation(
            $chat,
            new Pacing(
                humanize: true,
                minTypingMs: 0,
                maxTypingMs: 0,
                typingMsPerCharacter: 0.0,
                typingPauseChancePercent: 100,
                minTypingPauseMs: 10,
                maxTypingPauseMs: 10,
                cooldownMinMs: 0,
                cooldownMaxMs: 0,
                maxMessagesPerWindow: 0,
            ),
            static function (int $ms) use (&$sleeps): void {
                $sleeps[] = $ms;
            },
        );

        $conversation->send('Hello world');

        $this->assertSame([
            '/api/sendSeen',
            '/api/startTyping',
            '/api/stopTyping',
            '/api/startTyping',
            '/api/stopTyping',
            '/api/sendText',
        ], array_column($fake->requests, 'endpoint'));
        $this->assertSame([10], $sleeps);
    }

    public function test_conversation_wait_sleeps_and_is_chainable(): void
    {
        [$chat] = $this->makeChat();

        $sleeps = [];
        $conversation = new Conversation(
            $chat,
            Pacing::off(),
            static function (int $ms) use (&$sleeps): void {
                $sleeps[] = $ms;
            },
        );

        $this->assertSame($conversation, $conversation->wait(25));
        $this->assertSame([25], $sleeps);
    }

    public function test_conversation_respects_cooldown_between_messages(): void
    {
        [$chat] = $this->makeChat();

        $sleeps = [];
        $conversation = new Conversation(
            $chat,
            new Pacing(
                humanize: false,
                cooldownMinMs: 50,
                cooldownMaxMs: 50,
                maxMessagesPerWindow: 0,
            ),
            static function (int $ms) use (&$sleeps): void {
                $sleeps[] = $ms;
            },
        );

        $conversation->send('first');
        $conversation->send('second');

        $this->assertCount(1, $sleeps);
        $this->assertGreaterThan(0, $sleeps[0]);
    }

    public function test_conversation_throws_when_window_limit_is_reached(): void
    {
        [$chat] = $this->makeChat();

        $conversation = new Conversation(
            $chat,
            new Pacing(
                humanize: false,
                cooldownMinMs: 0,
                cooldownMaxMs: 0,
                maxMessagesPerWindow: 1,
                windowSeconds: 60,
            ),
        );

        $conversation->send('first');

        $this->expectException(ConversationThrottledException::class);
        $conversation->send('second');
    }

    public function test_conversation_reset_clears_window_limit(): void
    {
        [$chat] = $this->makeChat();

        $conversation = new Conversation(
            $chat,
            new Pacing(
                humanize: false,
                cooldownMinMs: 0,
                cooldownMaxMs: 0,
                maxMessagesPerWindow: 1,
                windowSeconds: 60,
            ),
        );

        $conversation->send('first');
        $conversation->reset();

        $this->assertInstanceOf(Message::class, $conversation->send('second'));
    }

    public function test_conversation_reply_passes_reply_to(): void
    {
        [$chat, $fake] = $this->makeChat();

        $conversation = new Conversation($chat, Pacing::off());

        $message = $conversation->reply('Hello back', 'false_11111111111@c.us_IN');

        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame('/api/sendText', $fake->requests[0]['endpoint']);
        $this->assertSame('false_11111111111@c.us_IN', $fake->requests[0]['payload']['reply_to']);
    }

    public function test_chat_exposes_conversation_handle(): void
    {
        [$chat] = $this->makeChat();

        $conversation = $chat->conversation();

        $this->assertInstanceOf(Conversation::class, $conversation);
        $this->assertSame($chat->chatId(), $conversation->chatId());
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
        );

        return [$chat, $fake];
    }
}
