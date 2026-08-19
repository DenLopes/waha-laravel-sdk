<?php

declare(strict_types=1);

namespace DenLopes\Waha\Contracts;

use DenLopes\Waha\Data\Input\BinaryFile;
use DenLopes\Waha\Data\Input\LinkPreview;
use DenLopes\Waha\Data\Input\MessagePoll;
use DenLopes\Waha\Data\Input\RemoteFile;
use DenLopes\Waha\Data\Input\SendListMessage;
use DenLopes\Waha\Data\Input\VideoBinaryFile;
use DenLopes\Waha\Data\Input\VideoRemoteFile;
use DenLopes\Waha\Data\Input\VoiceBinaryFile;
use DenLopes\Waha\Data\Input\VoiceRemoteFile;
use DenLopes\Waha\Session;
use DenLopes\Waha\Support\Pacing;

interface Chat
{
    public function session(): Session;

    public function chatId(): string;

    /**
     * Get a lazy message handle (no network call is made until you call get()).
     */
    public function message(string $id): Message;

    /**
     * Get a human-like conversation handle (no network call is made).
     */
    public function conversation(?Pacing $policy = null): Conversation;

    public function sendMessage(
        string $text,
        ?string $replyTo = null,
        ?bool $linkPreview = true,
        ?string $id = null,
        ?bool $linkPreviewHighQuality = false,
    ): Message;

    public function sendImage(
        RemoteFile|BinaryFile $file,
        ?string $caption = null,
        ?string $replyTo = null,
    ): Message;

    public function sendFile(
        RemoteFile|BinaryFile $file,
        ?string $caption = null,
        ?string $replyTo = null,
    ): Message;

    public function sendVoice(
        VoiceRemoteFile|VoiceBinaryFile $file,
        bool $convert = true,
        ?string $replyTo = null,
    ): Message;

    public function sendVideo(
        VideoRemoteFile|VideoBinaryFile $file,
        bool $convert = true,
        ?string $caption = null,
        ?string $replyTo = null,
        ?bool $asNote = null,
    ): Message;

    public function sendPoll(MessagePoll $poll, ?string $replyTo = null, ?string $id = null): Message;

    public function sendLocation(
        float $latitude,
        float $longitude,
        string $title,
        ?string $replyTo = null,
        ?string $id = null,
    ): Message;

    public function sendContactVcard(array $contacts, ?string $replyTo = null, ?string $id = null): Message;

    public function sendList(SendListMessage $message, ?string $replyTo = null): Message;

    public function sendLinkCustomPreview(
        string $text,
        LinkPreview $preview,
        ?string $replyTo = null,
        ?bool $linkPreviewHighQuality = true,
    ): Message;

    public function startTyping(): static;

    public function stopTyping(): static;

    public function forward(string $messageId, ?string $id = null): Message;

    public function react(string $messageId, string $reaction): static;

    public function star(string $messageId, bool $star): static;

    public function find(string $id): Message;

    public function getMessages(?int $limit = 10, ?int $offset = null): array;

    public function markRead(?array $messageIds = null, ?string $participant = null): static;

    public function pinMessage(string $messageId, int $duration = 86400): static;

    public function unpinMessage(string $messageId): static;

    public function archive(): static;

    public function unarchive(): static;

    public function markUnread(): static;

    public function clearMessages(): static;

    public function delete(): static;
}
