<?php

declare(strict_types=1);

namespace DenLopes\Waha\Services;

use DenLopes\Waha\Concerns\SendsRequests;
use DenLopes\Waha\Data\Input\ChannelSearchByText;
use DenLopes\Waha\Data\Input\ChannelSearchByView;
use DenLopes\Waha\Data\Input\CreateChannelRequest;
use DenLopes\Waha\Data\Output\Channel;
use DenLopes\Waha\Data\Output\ChannelCategory;
use DenLopes\Waha\Data\Output\ChannelCountry;
use DenLopes\Waha\Data\Output\ChannelListResult;
use DenLopes\Waha\Data\Output\ChannelMessage;
use DenLopes\Waha\Data\Output\ChannelView;
use DenLopes\Waha\Enums\ChannelRole;
use DenLopes\Waha\Session;

class ChannelsService
{
    use SendsRequests;

    /**
     * Get the list of known channels.
     *
     * @return Channel[]
     */
    public function listChannels(Session $session, ?ChannelRole $role = null): array
    {
        $payload = [];

        if ($role !== null) {
            $payload['role'] = $role->value;
        }

        $data = $this->send('get', '/api/{session}/channels', $payload, 'Communication with WAHA failed while listing channels.', session: $session);

        return array_map(
            static fn (array $item) => Channel::fromArray($item),
            $data,
        );
    }

    /**
     * Create a new channel.
     */
    public function createChannel(Session $session, CreateChannelRequest $request): Channel
    {
        $data = $this->send('post', '/api/{session}/channels', $request->toArray(), 'Communication with WAHA failed while creating the channel.', session: $session);

        return Channel::fromArray($data);
    }

    /**
     * Delete a channel.
     */
    public function deleteChannel(Session $session, string $id): array
    {
        return $this->send('delete', "/api/{session}/channels/{$id}", [], 'Communication with WAHA failed while deleting the channel.', session: $session);
    }

    /**
     * Get the channel info by id or invite code.
     */
    public function getChannel(Session $session, string $id): Channel
    {
        $data = $this->send('get', "/api/{session}/channels/{$id}", [], 'Communication with WAHA failed while fetching the channel.', session: $session);

        return Channel::fromArray($data);
    }

    /**
     * Preview channel messages.
     *
     * @return ChannelMessage[]
     */
    public function previewChannelMessages(
        Session $session,
        string $id,
        bool $downloadMedia = false,
        int $limit = 10,
    ): array {
        $data = $this->send('get', "/api/{session}/channels/{$id}/messages/preview", [
            'downloadMedia' => $downloadMedia,
            'limit'         => $limit,
        ], 'Communication with WAHA failed while previewing channel messages.', session: $session);

        return array_map(
            static fn (array $item) => ChannelMessage::fromArray($item),
            $data,
        );
    }

    /**
     * Follow a channel.
     */
    public function followChannel(Session $session, string $id): array
    {
        return $this->send('post', "/api/{session}/channels/{$id}/follow", [], 'Communication with WAHA failed while following the channel.', session: $session);
    }

    /**
     * Unfollow a channel.
     */
    public function unfollowChannel(Session $session, string $id): array
    {
        return $this->send('post', "/api/{session}/channels/{$id}/unfollow", [], 'Communication with WAHA failed while unfollowing the channel.', session: $session);
    }

    /**
     * Mute a channel.
     */
    public function muteChannel(Session $session, string $id): array
    {
        return $this->send('post', "/api/{session}/channels/{$id}/mute", [], 'Communication with WAHA failed while muting the channel.', session: $session);
    }

    /**
     * Unmute a channel.
     */
    public function unmuteChannel(Session $session, string $id): array
    {
        return $this->send('post', "/api/{session}/channels/{$id}/unmute", [], 'Communication with WAHA failed while unmuting the channel.', session: $session);
    }

    /**
     * Search for channels by view.
     */
    public function searchChannelsByView(Session $session, ChannelSearchByView $request): ChannelListResult
    {
        $data = $this->send('post', '/api/{session}/channels/search/by-view', $request->toArray(), 'Communication with WAHA failed while searching channels by view.', session: $session);

        return ChannelListResult::fromArray($data);
    }

    /**
     * Search for channels by text.
     */
    public function searchChannelsByText(Session $session, ChannelSearchByText $request): ChannelListResult
    {
        $data = $this->send('post', '/api/{session}/channels/search/by-text', $request->toArray(), 'Communication with WAHA failed while searching channels by text.', session: $session);

        return ChannelListResult::fromArray($data);
    }

    /**
     * Get the list of views for channel search.
     *
     * @return ChannelView[]
     */
    public function getSearchViews(Session $session): array
    {
        $data = $this->send('get', '/api/{session}/channels/search/views', [], 'Communication with WAHA failed while fetching channel search views.', session: $session);

        return array_map(
            static fn (array $item) => ChannelView::fromArray($item),
            $data,
        );
    }

    /**
     * Get the list of countries for channel search.
     *
     * @return ChannelCountry[]
     */
    public function getSearchCountries(Session $session): array
    {
        $data = $this->send('get', '/api/{session}/channels/search/countries', [], 'Communication with WAHA failed while fetching channel search countries.', session: $session);

        return array_map(
            static fn (array $item) => ChannelCountry::fromArray($item),
            $data,
        );
    }

    /**
     * Get the list of categories for channel search.
     *
     * @return ChannelCategory[]
     */
    public function getSearchCategories(Session $session): array
    {
        $data = $this->send('get', '/api/{session}/channels/search/categories', [], 'Communication with WAHA failed while fetching channel search categories.', session: $session);

        return array_map(
            static fn (array $item) => ChannelCategory::fromArray($item),
            $data,
        );
    }
}
