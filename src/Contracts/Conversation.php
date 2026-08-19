<?php

declare(strict_types=1);

namespace DenLopes\Waha\Contracts;

use DenLopes\Waha\Session;
use DenLopes\Waha\Support\Pacing;

interface Conversation
{
    public function chat(): Chat;

    public function session(): Session;

    public function chatId(): string;

    public function send(
        string $text,
        ?string $replyTo = null,
        ?bool $linkPreview = true,
        ?string $id = null,
        ?bool $linkPreviewHighQuality = false,
    ): Message;

    public function reply(
        string $text,
        string $messageId,
        ?bool $linkPreview = true,
        ?string $id = null,
        ?bool $linkPreviewHighQuality = false,
    ): Message;

    public function markRead(?array $messageIds = null, ?string $participant = null): static;

    public function startTyping(): static;

    public function stopTyping(): static;

    public function wait(int $milliseconds): static;

    public function reset(): static;

    public function policy(): Pacing;
}
