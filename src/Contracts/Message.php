<?php

declare(strict_types=1);

namespace DenLopes\Waha\Contracts;

use DenLopes\Waha\Data\Output\MessageData;
use DenLopes\Waha\Session;

interface Message
{
    public function session(): Session;

    public function chatId(): string;

    public function id(): string;

    public function get(): MessageData;

    public function toArray(): array;

    public function toJson(int $flags = 0): string;

    public function refresh(): static;

    public function markRead(): static;

    public function react(string $reaction): static;

    public function star(bool $star = true): static;

    public function pin(int $duration = 86400): static;

    public function unpin(): static;

    public function update(
        string $text,
        ?bool $linkPreview = null,
        ?bool $linkPreviewHighQuality = null,
    ): static;

    public function forward(string $chatId, ?string $id = null): Message;

    public function delete(): static;
}
