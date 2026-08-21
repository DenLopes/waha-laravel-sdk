<?php

declare(strict_types=1);

namespace DenLopes\Waha\Contracts;

use DenLopes\Waha\Enums\ContactStage;

/**
 * Persists the relationship stage of a contact per session.
 */
interface ContactStageStore
{
    public function get(string $session, string $chatId): ?ContactStage;

    public function mark(string $session, string $chatId, ContactStage $stage, ?int $ttlSeconds = null): void;

    public function forget(string $session, string $chatId): void;
}
