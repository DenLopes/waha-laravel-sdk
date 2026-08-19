<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\WahaData;
use DenLopes\Waha\Enums\WahaEngineEnum;
use DenLopes\Waha\Enums\WahaWebhookEventEnum;

/**
 * A WAHA webhook delivery.
 *
 * The `payload` is automatically parsed into the most specific DTO for the
 * event. Consumers can inspect {@see self::$event} and narrow `payload`
 * accordingly; events without a dedicated payload DTO (or unrecognized events)
 * keep their raw array.
 */
final readonly class WebhookData extends WahaData
{
    /**
     * @param  array<string, mixed>|null  $metadata  Session metadata.
     */
    public function __construct(
        public string $id,
        public int $timestamp,
        public string $session,
        public ?array $metadata,
        public ?WahaEngineEnum $engine,
        public ?WahaWebhookEventEnum $event,
        public WahaData|array|null $payload,
        public ?MeInfoData $me,
        public ?WahaEnvironmentData $environment,
    ) {}

    public static function fromArray(array $data): static
    {
        $event = WahaWebhookEventEnum::tryFrom((string) ($data['event'] ?? ''));

        return new self(
            id: (string) ($data['id'] ?? ''),
            timestamp: (int) ($data['timestamp'] ?? 0),
            session: (string) ($data['session'] ?? ''),
            metadata: $data['metadata'] ?? null,
            engine: WahaEngineEnum::tryFrom((string) ($data['engine'] ?? '')),
            event: $event,
            payload: self::parsePayload($event, $data['payload'] ?? null),
            me: isset($data['me']) && is_array($data['me'])
                ? MeInfoData::fromArray($data['me'])
                : null,
            environment: isset($data['environment']) && is_array($data['environment'])
                ? WahaEnvironmentData::fromArray($data['environment'])
                : null,
        );
    }

    /**
     * Map a webhook event to its typed payload DTO.
     */
    private static function parsePayload(?WahaWebhookEventEnum $event, mixed $payload): WahaData|array|null
    {
        if (!is_array($payload)) {
            return $payload;
        }

        return match ($event) {
            WahaWebhookEventEnum::SESSION_STATUS => WASessionStatusBodyData::fromArray($payload),
            WahaWebhookEventEnum::MESSAGE,
            WahaWebhookEventEnum::MESSAGE_ANY      => WAMessageData::fromArray($payload),
            WahaWebhookEventEnum::MESSAGE_REACTION => WAMessageReactionData::fromArray($payload),
            WahaWebhookEventEnum::MESSAGE_ACK,
            WahaWebhookEventEnum::MESSAGE_ACK_GROUP     => WAMessageAckBodyData::fromArray($payload),
            WahaWebhookEventEnum::MESSAGE_REVOKED       => WAMessageRevokedBodyData::fromArray($payload),
            WahaWebhookEventEnum::MESSAGE_EDITED        => WAMessageEditedBodyData::fromArray($payload),
            WahaWebhookEventEnum::GROUP_V2_JOIN         => GroupV2JoinEventData::fromArray($payload),
            WahaWebhookEventEnum::GROUP_V2_LEAVE        => GroupV2LeaveEventData::fromArray($payload),
            WahaWebhookEventEnum::GROUP_V2_UPDATE       => GroupV2UpdateEventData::fromArray($payload),
            WahaWebhookEventEnum::GROUP_V2_PARTICIPANTS => GroupV2ParticipantsEventData::fromArray($payload),
            WahaWebhookEventEnum::PRESENCE_UPDATE       => WAHAChatPresencesData::fromArray($payload),
            WahaWebhookEventEnum::POLL_VOTE,
            WahaWebhookEventEnum::POLL_VOTE_FAILED => PollVotePayloadData::fromArray($payload),
            WahaWebhookEventEnum::CHAT_ARCHIVE     => ChatArchiveEventData::fromArray($payload),
            WahaWebhookEventEnum::CALL_RECEIVED,
            WahaWebhookEventEnum::CALL_ACCEPTED,
            WahaWebhookEventEnum::CALL_REJECTED => CallDataData::fromArray($payload),
            WahaWebhookEventEnum::LABEL_UPSERT,
            WahaWebhookEventEnum::LABEL_DELETED => LabelData::fromArray($payload),
            WahaWebhookEventEnum::LABEL_CHAT_ADDED,
            WahaWebhookEventEnum::LABEL_CHAT_DELETED => LabelChatAssociationData::fromArray($payload),
            WahaWebhookEventEnum::EVENT_RESPONSE,
            WahaWebhookEventEnum::EVENT_RESPONSE_FAILED => EventResponsePayloadData::fromArray($payload),
            WahaWebhookEventEnum::ENGINE_EVENT          => EnginePayloadData::fromArray($payload),
            default                                     => $payload,
        };
    }
}
