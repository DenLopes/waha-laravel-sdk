<?php

declare(strict_types=1);

namespace DenLopes\Waha\Tests\Support;

use DenLopes\Waha\Contracts\ContactStageStore;
use DenLopes\Waha\Enums\ContactStage;

/**
 * In-memory {@see ContactStageStore} for pipeline tests.
 */
final class FakeContactStageStore implements ContactStageStore
{
    /**
     * @var array<string, ContactStage>
     */
    public array $stages = [];

    /**
     * @var list<array{session: string, chatId: string, stage: ContactStage}>
     */
    public array $marked = [];

    public function get(string $session, string $chatId): ?ContactStage
    {
        return $this->stages[$session.':'.$chatId] ?? null;
    }

    public function mark(string $session, string $chatId, ContactStage $stage, ?int $ttlSeconds = null): void
    {
        $this->stages[$session.':'.$chatId] = $stage;
        $this->marked[] = compact('session', 'chatId', 'stage');
    }

    public function forget(string $session, string $chatId): void
    {
        unset($this->stages[$session.':'.$chatId]);
    }
}
