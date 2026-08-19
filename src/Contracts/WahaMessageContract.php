<?php

declare(strict_types=1);

namespace DenLopes\Waha\Contracts;

use DenLopes\Waha\Data\Output\WAMessageData;
use DenLopes\Waha\Support\WahaSession;

interface WahaMessageContract
{
    public function session(): WahaSession;

    public function chatId(): string;

    public function id(): string;

    public function get(): WAMessageData;

    public function toArray(): array;

    public function toJson(int $flags = 0): string;

    public function refresh(): static;

    public function read(): static;

    public function react(string $reaction): static;

    public function star(bool $star = true): static;

    public function pin(int $duration = 86400): static;

    public function unpin(): static;

    public function update(
        string $text,
        ?bool $linkPreview = null,
        ?bool $linkPreviewHighQuality = null,
    ): static;

    public function forward(string $chatId, ?string $id = null): WahaMessageContract;

    public function delete(): static;
}
