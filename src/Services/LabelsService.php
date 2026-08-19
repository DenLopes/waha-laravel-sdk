<?php

declare(strict_types=1);

namespace DenLopes\Waha\Services;

use DenLopes\Waha\Concerns\SendsWahaRequests;
use DenLopes\Waha\Data\Input\LabelBodyData;
use DenLopes\Waha\Data\Input\SetLabelsRequestData;
use DenLopes\Waha\Data\Output\ChatData;
use DenLopes\Waha\Data\Output\LabelData;
use DenLopes\Waha\Support\WahaSession;

class LabelsService
{
    use SendsWahaRequests;

    /**
     * Get all labels.
     *
     * @return LabelData[]
     */
    public function getLabels(WahaSession $session): array
    {
        $data = $this->send('get', '/api/{session}/labels', [], 'Communication with WAHA failed while fetching labels.', session: $session);

        return array_map(
            static fn (array $item) => LabelData::fromArray($item),
            $data,
        );
    }

    /**
     * Create a label.
     */
    public function createLabel(WahaSession $session, LabelBodyData $body): LabelData
    {
        $data = $this->send('post', '/api/{session}/labels', $body->toArray(), 'Communication with WAHA failed while creating the label.', session: $session);

        return LabelData::fromArray($data);
    }

    /**
     * Update a label.
     */
    public function updateLabel(WahaSession $session, string $labelId, LabelBodyData $body): LabelData
    {
        $data = $this->send('put', "/api/{session}/labels/{$labelId}", $body->toArray(), 'Communication with WAHA failed while updating the label.', session: $session);

        return LabelData::fromArray($data);
    }

    /**
     * Delete a label.
     */
    public function deleteLabel(WahaSession $session, string $labelId): array
    {
        return $this->send('delete', "/api/{session}/labels/{$labelId}", [], 'Communication with WAHA failed while deleting the label.', session: $session);
    }

    /**
     * Get labels for a chat.
     *
     * @return LabelData[]
     */
    public function getChatLabels(WahaSession $session, string $chatId): array
    {
        $data = $this->send('get', "/api/{session}/labels/chats/{$chatId}", [], 'Communication with WAHA failed while fetching the chat labels.', session: $session);

        return array_map(
            static fn (array $item) => LabelData::fromArray($item),
            $data,
        );
    }

    /**
     * Save labels for a chat.
     */
    public function setChatLabels(WahaSession $session, string $chatId, SetLabelsRequestData $labels): array
    {
        return $this->send('put', "/api/{session}/labels/chats/{$chatId}", $labels->toArray(), 'Communication with WAHA failed while setting the chat labels.', session: $session);
    }

    /**
     * Get chats by label.
     *
     * @return ChatData[]
     */
    public function getChatsByLabel(WahaSession $session, string $labelId): array
    {
        $data = $this->send('get', "/api/{session}/labels/{$labelId}/chats", [], 'Communication with WAHA failed while fetching the chats by label.', session: $session);

        return array_map(
            static fn (array $item) => ChatData::fromArray($item),
            $data,
        );
    }
}
