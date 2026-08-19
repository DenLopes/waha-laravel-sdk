<?php

declare(strict_types=1);

namespace DenLopes\Waha\Services;

use DenLopes\Waha\Concerns\SendsRequests;
use DenLopes\Waha\Data\Input\BinaryFile;
use DenLopes\Waha\Data\Input\Button;
use DenLopes\Waha\Data\Input\Contact;
use DenLopes\Waha\Data\Input\LinkPreview;
use DenLopes\Waha\Data\Input\MessagePoll;
use DenLopes\Waha\Data\Input\MessagePollVoteRequest;
use DenLopes\Waha\Data\Input\RemoteFile;
use DenLopes\Waha\Data\Input\SendListMessage;
use DenLopes\Waha\Data\Input\VCardContact;
use DenLopes\Waha\Data\Input\VideoBinaryFile;
use DenLopes\Waha\Data\Input\VideoRemoteFile;
use DenLopes\Waha\Data\Input\VoiceBinaryFile;
use DenLopes\Waha\Data\Input\VoiceRemoteFile;
use DenLopes\Waha\Data\Output\MessageData;
use DenLopes\Waha\Data\Output\NewMessageId;
use DenLopes\Waha\Data\Output\NumberExistResult;
use DenLopes\Waha\Enums\Ack;
use DenLopes\Waha\Enums\MessageSortField;
use DenLopes\Waha\Enums\SortOrder;
use DenLopes\Waha\Session;

class MessagingService
{
    use SendsRequests;

    /**
     * Send a text message.
     */
    public function sendText(
        string $chatId,
        string $text,
        ?Session $session = null,
        ?string $replyTo = null,
        ?bool $linkPreview = true,
        ?string $id = null,
        ?bool $linkPreviewHighQuality = false,
    ): MessageData {
        $payload = [
            'chatId'  => $chatId,
            'text'    => $text,
            'session' => $this->session($session),
        ];

        if ($replyTo !== null) {
            $payload['reply_to'] = $replyTo;
        }

        if ($linkPreview !== null) {
            $payload['linkPreview'] = $linkPreview;
        }

        if ($linkPreviewHighQuality !== null) {
            $payload['linkPreviewHighQuality'] = $linkPreviewHighQuality;
        }

        if ($id !== null) {
            $payload['id'] = $id;
        }

        $data = $this->send('post', '/api/sendText', $payload, 'Communication with WAHA failed while sending the text message.');

        return MessageData::fromArray($data);
    }

    /**
     * Send a text message via the deprecated GET endpoint.
     */
    public function sendTextGet(string $phone, string $text, ?Session $session = null): array
    {
        return $this->send('get', '/api/sendText', [
            'phone'   => $phone,
            'text'    => $text,
            'session' => $this->session($session),
        ], 'Communication with WAHA failed while sending the text message.');
    }

    /**
     * Send an image.
     */
    public function sendImage(
        string $chatId,
        RemoteFile|BinaryFile $file,
        ?string $caption = null,
        ?Session $session = null,
        ?string $replyTo = null,
    ): MessageData {
        $payload = [
            'chatId'  => $chatId,
            'file'    => $file->toArray(),
            'session' => $this->session($session),
        ];

        if ($caption !== null) {
            $payload['caption'] = $caption;
        }

        if ($replyTo !== null) {
            $payload['reply_to'] = $replyTo;
        }

        return MessageData::fromArray($this->send('post', '/api/sendImage', $payload, 'Communication with WAHA failed while sending the image.'));
    }

    /**
     * Send a file.
     */
    public function sendFile(
        string $chatId,
        RemoteFile|BinaryFile $file,
        ?string $caption = null,
        ?Session $session = null,
        ?string $replyTo = null,
    ): MessageData {
        $payload = [
            'chatId'  => $chatId,
            'file'    => $file->toArray(),
            'session' => $this->session($session),
        ];

        if ($caption !== null) {
            $payload['caption'] = $caption;
        }

        if ($replyTo !== null) {
            $payload['reply_to'] = $replyTo;
        }

        return MessageData::fromArray($this->send('post', '/api/sendFile', $payload, 'Communication with WAHA failed while sending the file.'));
    }

    /**
     * Send a voice message.
     */
    public function sendVoice(
        string $chatId,
        VoiceRemoteFile|VoiceBinaryFile $file,
        bool $convert = true,
        ?Session $session = null,
        ?string $replyTo = null,
    ): MessageData {
        $payload = [
            'chatId'  => $chatId,
            'file'    => $file->toArray(),
            'convert' => $convert,
            'session' => $this->session($session),
        ];

        if ($replyTo !== null) {
            $payload['reply_to'] = $replyTo;
        }

        return MessageData::fromArray($this->send('post', '/api/sendVoice', $payload, 'Communication with WAHA failed while sending the voice message.'));
    }

    /**
     * Send a video.
     */
    public function sendVideo(
        string $chatId,
        VideoRemoteFile|VideoBinaryFile $file,
        bool $convert = true,
        ?string $caption = null,
        ?Session $session = null,
        ?string $replyTo = null,
        ?bool $asNote = null,
    ): MessageData {
        $payload = [
            'chatId'  => $chatId,
            'file'    => $file->toArray(),
            'convert' => $convert,
            'session' => $this->session($session),
        ];

        if ($caption !== null) {
            $payload['caption'] = $caption;
        }

        if ($replyTo !== null) {
            $payload['reply_to'] = $replyTo;
        }

        if ($asNote !== null) {
            $payload['asNote'] = $asNote;
        }

        return MessageData::fromArray($this->send('post', '/api/sendVideo', $payload, 'Communication with WAHA failed while sending the video.'));
    }

    /**
     * Send an interactive buttons message (deprecated).
     *
     * @param  Button[]  $buttons
     */
    public function sendButtons(
        string $chatId,
        string $header,
        string $body,
        string $footer,
        array $buttons,
        ?Session $session = null,
        RemoteFile|BinaryFile|null $headerImage = null,
    ): MessageData {
        $payload = [
            'chatId'  => $chatId,
            'header'  => $header,
            'body'    => $body,
            'footer'  => $footer,
            'buttons' => array_map(
                static fn (Button $button) => $button->toArray(),
                $buttons,
            ),
            'session' => $this->session($session),
        ];

        if ($headerImage !== null) {
            $payload['headerImage'] = $headerImage->toArray();
        }

        return MessageData::fromArray($this->send('post', '/api/sendButtons', $payload, 'Communication with WAHA failed while sending the buttons.'));
    }

    /**
     * Send a list (interactive) message.
     */
    public function sendList(
        string $chatId,
        SendListMessage $message,
        ?Session $session = null,
        ?string $replyTo = null,
    ): MessageData {
        $payload = [
            'chatId'  => $chatId,
            'message' => $message->toArray(),
            'session' => $this->session($session),
        ];

        if ($replyTo !== null) {
            $payload['reply_to'] = $replyTo;
        }

        return MessageData::fromArray($this->send('post', '/api/sendList', $payload, 'Communication with WAHA failed while sending the list.'));
    }

    /**
     * Send a poll.
     */
    public function sendPoll(
        string $chatId,
        MessagePoll $poll,
        ?Session $session = null,
        ?string $replyTo = null,
        ?string $id = null,
    ): MessageData {
        $payload = [
            'chatId'  => $chatId,
            'poll'    => $poll->toArray(),
            'session' => $this->session($session),
        ];

        if ($replyTo !== null) {
            $payload['reply_to'] = $replyTo;
        }

        if ($id !== null) {
            $payload['id'] = $id;
        }

        return MessageData::fromArray($this->send('post', '/api/sendPoll', $payload, 'Communication with WAHA failed while sending the poll.'));
    }

    /**
     * Vote on a poll.
     */
    public function sendPollVote(MessagePollVoteRequest $request, ?Session $session = null): array
    {
        $payload = array_merge($request->toArray(), [
            'session' => $this->session($session),
        ]);

        return $this->send('post', '/api/sendPollVote', $payload, 'Communication with WAHA failed while voting on the poll.');
    }

    /**
     * Send a location.
     */
    public function sendLocation(
        string $chatId,
        float $latitude,
        float $longitude,
        string $title,
        ?Session $session = null,
        ?string $replyTo = null,
        ?string $id = null,
    ): MessageData {
        $payload = [
            'chatId'    => $chatId,
            'latitude'  => $latitude,
            'longitude' => $longitude,
            'title'     => $title,
            'session'   => $this->session($session),
        ];

        if ($replyTo !== null) {
            $payload['reply_to'] = $replyTo;
        }

        if ($id !== null) {
            $payload['id'] = $id;
        }

        return MessageData::fromArray($this->send('post', '/api/sendLocation', $payload, 'Communication with WAHA failed while sending the location.'));
    }

    /**
     * Send one or more contacts as vCards.
     *
     * @param  array<int, Contact|VCardContact>  $contacts
     */
    public function sendContactVcard(
        string $chatId,
        array $contacts,
        ?Session $session = null,
        ?string $replyTo = null,
        ?string $id = null,
    ): MessageData {
        $payload = [
            'chatId'   => $chatId,
            'contacts' => array_map(
                static fn (Contact|VCardContact $contact) => $contact->toArray(),
                $contacts,
            ),
            'session'  => $this->session($session),
        ];

        if ($replyTo !== null) {
            $payload['reply_to'] = $replyTo;
        }

        if ($id !== null) {
            $payload['id'] = $id;
        }

        return MessageData::fromArray($this->send('post', '/api/sendContactVcard', $payload, 'Communication with WAHA failed while sending the contact vCard.'));
    }

    /**
     * Send a text message with a custom link preview.
     */
    public function sendLinkCustomPreview(
        string $chatId,
        string $text,
        LinkPreview $preview,
        ?Session $session = null,
        ?string $replyTo = null,
        ?bool $linkPreviewHighQuality = true,
    ): MessageData {
        $payload = [
            'chatId'  => $chatId,
            'text'    => $text,
            'preview' => $preview->toArray(),
            'session' => $this->session($session),
        ];

        if ($replyTo !== null) {
            $payload['reply_to'] = $replyTo;
        }

        if ($linkPreviewHighQuality !== null) {
            $payload['linkPreviewHighQuality'] = $linkPreviewHighQuality;
        }

        return MessageData::fromArray($this->send('post', '/api/send/link-custom-preview', $payload, 'Communication with WAHA failed while sending the link preview.'));
    }

    /**
     * Mark messages in a chat as seen.
     *
     * @param  string[]|null  $messageIds  Specific message IDs to mark as seen.
     */
    public function sendSeen(
        string $chatId,
        ?Session $session = null,
        ?array $messageIds = null,
        ?string $participant = null,
    ): array {
        $payload = [
            'chatId'  => $chatId,
            'session' => $this->session($session),
        ];

        if ($messageIds !== null) {
            $payload['messageIds'] = $messageIds;
        }

        if ($participant !== null) {
            $payload['participant'] = $participant;
        }

        return $this->send('post', '/api/sendSeen', $payload, 'Communication with WAHA failed while marking messages as seen.');
    }

    /**
     * Start typing in a chat.
     */
    public function startTyping(string $chatId, ?Session $session = null): array
    {
        return $this->send('post', '/api/startTyping', [
            'chatId'  => $chatId,
            'session' => $this->session($session),
        ], 'Communication with WAHA failed while starting typing.');
    }

    /**
     * Stop typing in a chat.
     */
    public function stopTyping(string $chatId, ?Session $session = null): array
    {
        return $this->send('post', '/api/stopTyping', [
            'chatId'  => $chatId,
            'session' => $this->session($session),
        ], 'Communication with WAHA failed while stopping typing.');
    }

    /**
     * React to a message with an emoji (empty string removes the reaction).
     */
    public function setReaction(string $messageId, string $reaction, ?Session $session = null): array
    {
        return $this->send('put', '/api/reaction', [
            'messageId' => $messageId,
            'reaction'  => $reaction,
            'session'   => $this->session($session),
        ], 'Communication with WAHA failed while setting the reaction.');
    }

    /**
     * Forward a message to another chat.
     */
    public function forwardMessage(
        string $chatId,
        string $messageId,
        ?Session $session = null,
        ?string $id = null,
    ): MessageData {
        $payload = [
            'chatId'    => $chatId,
            'messageId' => $messageId,
            'session'   => $this->session($session),
        ];

        if ($id !== null) {
            $payload['id'] = $id;
        }

        $data = $this->send('post', '/api/forwardMessage', $payload, 'Communication with WAHA failed while forwarding the message.');

        return MessageData::fromArray($data);
    }

    /**
     * Generate a new message ID for a session.
     */
    public function getNewMessageId(Session $session): NewMessageId
    {
        $data = $this->send('get', '/api/{session}/new-message-id', [], 'Communication with WAHA failed while generating a message ID.', session: $session);

        return NewMessageId::fromArray($data);
    }

    /**
     * Star or unstar a message.
     */
    public function setStar(string $messageId, string $chatId, bool $star, ?Session $session = null): array
    {
        return $this->send('put', '/api/star', [
            'messageId' => $messageId,
            'chatId'    => $chatId,
            'star'      => $star,
            'session'   => $this->session($session),
        ], 'Communication with WAHA failed while starring the message.');
    }

    /**
     * Reply on a button message.
     */
    public function sendButtonsReply(
        string $chatId,
        string $selectedDisplayText,
        string $selectedButtonID,
        ?Session $session = null,
        ?string $replyTo = null,
    ): array {
        $payload = [
            'chatId'              => $chatId,
            'selectedDisplayText' => $selectedDisplayText,
            'selectedButtonID'    => $selectedButtonID,
            'session'             => $this->session($session),
        ];

        if ($replyTo !== null) {
            $payload['replyTo'] = $replyTo;
        }

        return $this->send('post', '/api/send/buttons/reply', $payload, 'Communication with WAHA failed while replying to the button message.');
    }

    /**
     * Get messages in a chat via the deprecated endpoint.
     *
     * @return MessageData[]
     */
    public function getMessages(
        string $chatId,
        ?Session $session = null,
        int $limit = 10,
        ?int $offset = null,
        ?MessageSortField $sortBy = null,
        ?SortOrder $sortOrder = null,
        ?bool $downloadMedia = true,
        ?bool $merge = true,
        ?int $timestampLte = null,
        ?int $timestampGte = null,
        ?bool $fromMe = null,
        ?Ack $ack = null,
    ): array {
        $payload = [
            'chatId'  => $chatId,
            'session' => $this->session($session),
            'limit'   => $limit,
        ];

        if ($offset !== null) {
            $payload['offset'] = $offset;
        }

        if ($sortBy !== null) {
            $payload['sortBy'] = $sortBy->value;
        }

        if ($sortOrder !== null) {
            $payload['sortOrder'] = $sortOrder->value;
        }

        if ($downloadMedia !== null) {
            $payload['downloadMedia'] = $downloadMedia;
        }

        if ($merge !== null) {
            $payload['merge'] = $merge;
        }

        if ($timestampLte !== null) {
            $payload['filter.timestamp.lte'] = $timestampLte;
        }

        if ($timestampGte !== null) {
            $payload['filter.timestamp.gte'] = $timestampGte;
        }

        if ($fromMe !== null) {
            $payload['filter.fromMe'] = $fromMe;
        }

        if ($ack !== null) {
            $payload['filter.ack'] = $ack->value;
        }

        $data = $this->send('get', '/api/messages', $payload, 'Communication with WAHA failed while fetching messages.');

        return array_map(
            static fn (array $item) => MessageData::fromArray($item),
            $data,
        );
    }

    /**
     * Check whether a phone number is registered in WhatsApp (deprecated).
     */
    public function checkNumberStatus(string $phone, ?Session $session = null): NumberExistResult
    {
        $data = $this->send('get', '/api/checkNumberStatus', [
            'phone'   => $phone,
            'session' => $this->session($session),
        ], 'Communication with WAHA failed while checking the number.');

        return NumberExistResult::fromArray($data);
    }

    /**
     * Reply to a message via the deprecated endpoint.
     */
    public function reply(
        string $chatId,
        string $text,
        ?Session $session = null,
        ?string $replyTo = null,
        ?string $id = null,
        ?bool $linkPreview = true,
        ?bool $linkPreviewHighQuality = false,
    ): MessageData {
        $payload = [
            'chatId'  => $chatId,
            'text'    => $text,
            'session' => $this->session($session),
        ];

        if ($replyTo !== null) {
            $payload['reply_to'] = $replyTo;
        }

        if ($id !== null) {
            $payload['id'] = $id;
        }

        if ($linkPreview !== null) {
            $payload['linkPreview'] = $linkPreview;
        }

        if ($linkPreviewHighQuality !== null) {
            $payload['linkPreviewHighQuality'] = $linkPreviewHighQuality;
        }

        return MessageData::fromArray($this->send('post', '/api/reply', $payload, 'Communication with WAHA failed while replying.'));
    }

    /**
     * Send a link preview via the deprecated endpoint.
     */
    public function sendLinkPreview(
        string $chatId,
        string $url,
        string $title,
        ?Session $session = null,
        ?string $id = null,
    ): MessageData {
        $payload = [
            'chatId'  => $chatId,
            'url'     => $url,
            'title'   => $title,
            'session' => $this->session($session),
        ];

        if ($id !== null) {
            $payload['id'] = $id;
        }

        return MessageData::fromArray($this->send('post', '/api/sendLinkPreview', $payload, 'Communication with WAHA failed while sending the link preview.'));
    }
}
