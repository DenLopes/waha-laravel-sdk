<?php

declare(strict_types=1);

namespace DenLopes\Waha\Contracts;

use DenLopes\Waha\Data\Input\BinaryFileData;
use DenLopes\Waha\Data\Input\LinkPreviewData;
use DenLopes\Waha\Data\Input\MessagePollData;
use DenLopes\Waha\Data\Input\RemoteFileData;
use DenLopes\Waha\Data\Input\SendListMessageData;
use DenLopes\Waha\Data\Input\VideoBinaryFileData;
use DenLopes\Waha\Data\Input\VideoRemoteFileData;
use DenLopes\Waha\Data\Input\VoiceBinaryFileData;
use DenLopes\Waha\Data\Input\VoiceRemoteFileData;
use DenLopes\Waha\Support\WahaSession;

interface WahaChatContract
{
    public function session(): WahaSession;

    public function chatId(): string;

    /**
     * Get a lazy message handle (no network call is made until you call get()).
     */
    public function message(string $id): WahaMessageContract;

    public function sendMessage(
        string $text,
        ?string $replyTo = null,
        ?bool $linkPreview = true,
        ?string $id = null,
        ?bool $linkPreviewHighQuality = false,
    ): WahaMessageContract;

    public function sendImage(
        RemoteFileData|BinaryFileData $file,
        ?string $caption = null,
        ?string $replyTo = null,
    ): WahaMessageContract;

    public function sendFile(
        RemoteFileData|BinaryFileData $file,
        ?string $caption = null,
        ?string $replyTo = null,
    ): WahaMessageContract;

    public function sendVoice(
        VoiceRemoteFileData|VoiceBinaryFileData $file,
        bool $convert = true,
        ?string $replyTo = null,
    ): WahaMessageContract;

    public function sendVideo(
        VideoRemoteFileData|VideoBinaryFileData $file,
        bool $convert = true,
        ?string $caption = null,
        ?string $replyTo = null,
        ?bool $asNote = null,
    ): WahaMessageContract;

    public function sendPoll(MessagePollData $poll, ?string $replyTo = null, ?string $id = null): WahaMessageContract;

    public function sendLocation(
        float $latitude,
        float $longitude,
        string $title,
        ?string $replyTo = null,
        ?string $id = null,
    ): WahaMessageContract;

    public function sendContactVcard(array $contacts, ?string $replyTo = null, ?string $id = null): WahaMessageContract;

    public function sendList(SendListMessageData $message, ?string $replyTo = null): WahaMessageContract;

    public function sendLinkCustomPreview(
        string $text,
        LinkPreviewData $preview,
        ?string $replyTo = null,
        ?bool $linkPreviewHighQuality = true,
    ): WahaMessageContract;

    public function startTyping(): static;

    public function stopTyping(): static;

    public function forwardMessage(string $messageId, ?string $id = null): WahaMessageContract;

    public function setReaction(string $messageId, string $reaction): static;

    public function setStar(string $messageId, bool $star): static;

    public function getMessage(string $id): WahaMessageContract;

    public function getMessages(?int $limit = 10, ?int $offset = null): array;

    public function sendSeen(?array $messageIds = null, ?string $participant = null): static;

    public function pinMessage(string $messageId, int $duration = 86400): static;

    public function unpinMessage(string $messageId): static;

    public function archive(): static;

    public function unarchive(): static;

    public function markUnread(): static;

    public function clearMessages(): static;

    public function delete(): static;
}
