<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\Data;
use DenLopes\Waha\Enums\Engine;
use DenLopes\Waha\Enums\WebhookEventType;

/**
 * A WAHA webhook delivery.
 *
 * The `payload` is automatically parsed into the most specific DTO for the
 * event. Consumers can inspect {@see self::$event} and narrow `payload`
 * accordingly; events without a dedicated payload DTO (or unrecognized events)
 * keep their raw array.
 */
final readonly class Webhook extends Data
{
    /**
     * @param  array<string, mixed>|null  $metadata  Session metadata.
     */
    public function __construct(
        public string $id,
        public int $timestamp,
        public string $session,
        public ?array $metadata,
        public ?Engine $engine,
        public ?WebhookEventType $event,
        public Data|array|null $payload,
        public ?MeInfo $me,
        public ?Environment $environment,
    ) {}

    public static function fromArray(array $data): static
    {
        $event = WebhookEventType::tryFrom((string) ($data['event'] ?? ''));

        return new self(
            id: (string) ($data['id'] ?? ''),
            timestamp: (int) ($data['timestamp'] ?? 0),
            session: (string) ($data['session'] ?? ''),
            metadata: $data['metadata'] ?? null,
            engine: Engine::tryFrom((string) ($data['engine'] ?? '')),
            event: $event,
            payload: self::parsePayload($event, $data['payload'] ?? null),
            me: isset($data['me']) && is_array($data['me'])
                ? MeInfo::fromArray($data['me'])
                : null,
            environment: isset($data['environment']) && is_array($data['environment'])
                ? Environment::fromArray($data['environment'])
                : null,
        );
    }

    /**
     * Map a webhook event to its typed payload DTO.
     */
    private static function parsePayload(?WebhookEventType $event, mixed $payload): Data|array|null
    {
        if (!is_array($payload)) {
            return $payload;
        }

        return match ($event) {
            WebhookEventType::SESSION_STATUS => SessionStatusBody::fromArray($payload),
            WebhookEventType::MESSAGE,
            WebhookEventType::MESSAGE_ANY      => MessageData::fromArray($payload),
            WebhookEventType::MESSAGE_REACTION => MessageReaction::fromArray($payload),
            WebhookEventType::MESSAGE_ACK,
            WebhookEventType::MESSAGE_ACK_GROUP     => MessageAckBody::fromArray($payload),
            WebhookEventType::MESSAGE_REVOKED       => MessageRevokedBody::fromArray($payload),
            WebhookEventType::MESSAGE_EDITED        => MessageEditedBody::fromArray($payload),
            WebhookEventType::GROUP_V2_JOIN         => GroupV2JoinEvent::fromArray($payload),
            WebhookEventType::GROUP_V2_LEAVE        => GroupV2LeaveEvent::fromArray($payload),
            WebhookEventType::GROUP_V2_UPDATE       => GroupV2UpdateEvent::fromArray($payload),
            WebhookEventType::GROUP_V2_PARTICIPANTS => GroupV2ParticipantsEvent::fromArray($payload),
            WebhookEventType::PRESENCE_UPDATE       => ChatPresences::fromArray($payload),
            WebhookEventType::POLL_VOTE,
            WebhookEventType::POLL_VOTE_FAILED => PollVotePayload::fromArray($payload),
            WebhookEventType::CHAT_ARCHIVE     => ChatArchiveEvent::fromArray($payload),
            WebhookEventType::CALL_RECEIVED,
            WebhookEventType::CALL_ACCEPTED,
            WebhookEventType::CALL_REJECTED => Call::fromArray($payload),
            WebhookEventType::LABEL_UPSERT,
            WebhookEventType::LABEL_DELETED => Label::fromArray($payload),
            WebhookEventType::LABEL_CHAT_ADDED,
            WebhookEventType::LABEL_CHAT_DELETED => LabelChatAssociation::fromArray($payload),
            WebhookEventType::EVENT_RESPONSE,
            WebhookEventType::EVENT_RESPONSE_FAILED         => EventResponsePayload::fromArray($payload),
            WebhookEventType::ENGINE_EVENT                  => EnginePayload::fromArray($payload),
            default                                         => $payload,
        };
    }
}
