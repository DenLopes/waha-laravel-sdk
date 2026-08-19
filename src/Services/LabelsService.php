<?php

declare(strict_types=1);

namespace DenLopes\Waha\Services;

use DenLopes\Waha\Concerns\SendsRequests;
use DenLopes\Waha\Data\Input\LabelBody;
use DenLopes\Waha\Data\Input\SetLabelsRequest;
use DenLopes\Waha\Data\Output\ChatData;
use DenLopes\Waha\Data\Output\Label;
use DenLopes\Waha\Session;

class LabelsService
{
    use SendsRequests;

    /**
     * Get all labels.
     *
     * @return Label[]
     */
    public function getLabels(Session $session): array
    {
        $data = $this->send('get', '/api/{session}/labels', [], 'Communication with WAHA failed while fetching labels.', session: $session);

        return array_map(
            static fn (array $item) => Label::fromArray($item),
            $data,
        );
    }

    /**
     * Create a label.
     */
    public function createLabel(Session $session, LabelBody $body): Label
    {
        $data = $this->send('post', '/api/{session}/labels', $body->toArray(), 'Communication with WAHA failed while creating the label.', session: $session);

        return Label::fromArray($data);
    }

    /**
     * Update a label.
     */
    public function updateLabel(Session $session, string $labelId, LabelBody $body): Label
    {
        $data = $this->send('put', "/api/{session}/labels/{$labelId}", $body->toArray(), 'Communication with WAHA failed while updating the label.', session: $session);

        return Label::fromArray($data);
    }

    /**
     * Delete a label.
     */
    public function deleteLabel(Session $session, string $labelId): array
    {
        return $this->send('delete', "/api/{session}/labels/{$labelId}", [], 'Communication with WAHA failed while deleting the label.', session: $session);
    }

    /**
     * Get labels for a chat.
     *
     * @return Label[]
     */
    public function getChatLabels(Session $session, string $chatId): array
    {
        $data = $this->send('get', "/api/{session}/labels/chats/{$chatId}", [], 'Communication with WAHA failed while fetching the chat labels.', session: $session);

        return array_map(
            static fn (array $item) => Label::fromArray($item),
            $data,
        );
    }

    /**
     * Save labels for a chat.
     */
    public function setChatLabels(Session $session, string $chatId, SetLabelsRequest $labels): array
    {
        return $this->send('put', "/api/{session}/labels/chats/{$chatId}", $labels->toArray(), 'Communication with WAHA failed while setting the chat labels.', session: $session);
    }

    /**
     * Get chats by label.
     *
     * @return ChatData[]
     */
    public function getChatsByLabel(Session $session, string $labelId): array
    {
        $data = $this->send('get', "/api/{session}/labels/{$labelId}/chats", [], 'Communication with WAHA failed while fetching the chats by label.', session: $session);

        return array_map(
            static fn (array $item) => ChatData::fromArray($item),
            $data,
        );
    }
}
