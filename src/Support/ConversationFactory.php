<?php

declare(strict_types=1);

namespace DenLopes\Waha\Support;

use DenLopes\Waha\Contracts\CircuitBreaker;
use DenLopes\Waha\Contracts\ColdTargetLimiter;
use DenLopes\Waha\Contracts\ContactStageStore;
use DenLopes\Waha\Contracts\ConversationStateStore;
use DenLopes\Waha\Contracts\ReachoutGuard;
use DenLopes\Waha\Contracts\SessionRateLimiter;
use DenLopes\Waha\Contracts\WarmupTracker;
use DenLopes\Waha\Resources\Chat;
use DenLopes\Waha\Resources\Conversation;

/**
 * Assembles a fully-wired {@see Conversation} from its container collaborators.
 */
final class ConversationFactory
{
    public function __construct(
        private readonly ConversationStateStore $stateStore,
        private readonly ContactStageStore $contactStageStore,
        private readonly SessionRateLimiter $sessionLimiter,
        private readonly ColdTargetLimiter $coldTargetLimiter,
        private readonly ReachoutGuard $reachoutGuard,
        private readonly WarmupTracker $warmupTracker,
        private readonly CircuitBreaker $circuitBreaker,
        private readonly bool $throwOnColdUrls = false,
        private readonly int $circuitBreakerCooldownSeconds = 300,
    ) {}

    public function make(Chat $chat, ?Pacing $policy = null): Conversation
    {
        return new Conversation(
            chat: $chat,
            policy: $policy ?? Pacing::fromConfig(),
            stateStore: $this->stateStore,
            contactStageStore: $this->contactStageStore,
            sessionLimiter: $this->sessionLimiter,
            coldTargetLimiter: $this->coldTargetLimiter,
            reachoutGuard: $this->reachoutGuard,
            warmupTracker: $this->warmupTracker,
            circuitBreaker: $this->circuitBreaker,
            throwOnColdUrls: $this->throwOnColdUrls,
            circuitBreakerCooldownSeconds: $this->circuitBreakerCooldownSeconds,
        );
    }
}
