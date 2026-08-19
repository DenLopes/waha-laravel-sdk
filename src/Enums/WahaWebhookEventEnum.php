<?php

declare(strict_types=1);

namespace DenLopes\Waha\Enums;

/**
 * WAHA webhook event types.
 */
enum WahaWebhookEventEnum: string
{
    case SESSION_STATUS = 'session.status';
    case MESSAGE = 'message';
    case MESSAGE_REACTION = 'message.reaction';
    case MESSAGE_ANY = 'message.any';
    case MESSAGE_ACK = 'message.ack';
    case MESSAGE_ACK_GROUP = 'message.ack.group';
    case MESSAGE_WAITING = 'message.waiting';
    case MESSAGE_REVOKED = 'message.revoked';
    case MESSAGE_EDITED = 'message.edited';
    case STATE_CHANGE = 'state.change';
    case GROUP_JOIN = 'group.join';
    case GROUP_LEAVE = 'group.leave';
    case GROUP_V2_JOIN = 'group.v2.join';
    case GROUP_V2_LEAVE = 'group.v2.leave';
    case GROUP_V2_UPDATE = 'group.v2.update';
    case GROUP_V2_PARTICIPANTS = 'group.v2.participants';
    case PRESENCE_UPDATE = 'presence.update';
    case POLL_VOTE = 'poll.vote';
    case POLL_VOTE_FAILED = 'poll.vote.failed';
    case CHAT_ARCHIVE = 'chat.archive';
    case CALL_RECEIVED = 'call.received';
    case CALL_ACCEPTED = 'call.accepted';
    case CALL_REJECTED = 'call.rejected';
    case LABEL_UPSERT = 'label.upsert';
    case LABEL_DELETED = 'label.deleted';
    case LABEL_CHAT_ADDED = 'label.chat.added';
    case LABEL_CHAT_DELETED = 'label.chat.deleted';
    case EVENT_RESPONSE = 'event.response';
    case EVENT_RESPONSE_FAILED = 'event.response.failed';
    case ENGINE_EVENT = 'engine.event';
}
