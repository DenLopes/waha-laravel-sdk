<?php

declare(strict_types=1);

namespace DenLopes\Waha\Services;

use DenLopes\Waha\Concerns\SendsWahaRequests;
use DenLopes\Waha\Data\Input\ChannelSearchByTextData;
use DenLopes\Waha\Data\Input\ChannelSearchByViewData;
use DenLopes\Waha\Data\Input\CreateChannelRequestData;
use DenLopes\Waha\Data\Output\ChannelCategoryData;
use DenLopes\Waha\Data\Output\ChannelCountryData;
use DenLopes\Waha\Data\Output\ChannelData;
use DenLopes\Waha\Data\Output\ChannelListResultData;
use DenLopes\Waha\Data\Output\ChannelMessageData;
use DenLopes\Waha\Data\Output\ChannelViewData;
use DenLopes\Waha\Enums\WahaChannelRoleEnum;
use DenLopes\Waha\Support\WahaSession;

class ChannelsService
{
    use SendsWahaRequests;

    /**
     * Get the list of known channels.
     *
     * @return ChannelData[]
     */
    public function listChannels(WahaSession $session, ?WahaChannelRoleEnum $role = null): array
    {
        $payload = [];

        if ($role !== null) {
            $payload['role'] = $role->value;
        }

        $data = $this->send('get', "/api/{$this->session($session)}/channels", $payload, 'Communication with WAHA failed while listing channels.');

        return array_map(
            static fn (array $item) => ChannelData::fromArray($item),
            $data,
        );
    }

    /**
     * Create a new channel.
     */
    public function createChannel(WahaSession $session, CreateChannelRequestData $request): ChannelData
    {
        $data = $this->send('post', "/api/{$this->session($session)}/channels", $request->toArray(), 'Communication with WAHA failed while creating the channel.');

        return ChannelData::fromArray($data);
    }

    /**
     * Delete a channel.
     */
    public function deleteChannel(WahaSession $session, string $id): array
    {
        return $this->send('delete', "/api/{$this->session($session)}/channels/{$id}", [], 'Communication with WAHA failed while deleting the channel.');
    }

    /**
     * Get the channel info by id or invite code.
     */
    public function getChannel(WahaSession $session, string $id): ChannelData
    {
        $data = $this->send('get', "/api/{$this->session($session)}/channels/{$id}", [], 'Communication with WAHA failed while fetching the channel.');

        return ChannelData::fromArray($data);
    }

    /**
     * Preview channel messages.
     *
     * @return ChannelMessageData[]
     */
    public function previewChannelMessages(
        WahaSession $session,
        string $id,
        bool $downloadMedia = false,
        int $limit = 10,
    ): array {
        $data = $this->send('get', "/api/{$this->session($session)}/channels/{$id}/messages/preview", [
            'downloadMedia' => $downloadMedia,
            'limit'         => $limit,
        ], 'Communication with WAHA failed while previewing channel messages.');

        return array_map(
            static fn (array $item) => ChannelMessageData::fromArray($item),
            $data,
        );
    }

    /**
     * Follow a channel.
     */
    public function followChannel(WahaSession $session, string $id): array
    {
        return $this->send('post', "/api/{$this->session($session)}/channels/{$id}/follow", [], 'Communication with WAHA failed while following the channel.');
    }

    /**
     * Unfollow a channel.
     */
    public function unfollowChannel(WahaSession $session, string $id): array
    {
        return $this->send('post', "/api/{$this->session($session)}/channels/{$id}/unfollow", [], 'Communication with WAHA failed while unfollowing the channel.');
    }

    /**
     * Mute a channel.
     */
    public function muteChannel(WahaSession $session, string $id): array
    {
        return $this->send('post', "/api/{$this->session($session)}/channels/{$id}/mute", [], 'Communication with WAHA failed while muting the channel.');
    }

    /**
     * Unmute a channel.
     */
    public function unmuteChannel(WahaSession $session, string $id): array
    {
        return $this->send('post', "/api/{$this->session($session)}/channels/{$id}/unmute", [], 'Communication with WAHA failed while unmuting the channel.');
    }

    /**
     * Search for channels by view.
     */
    public function searchChannelsByView(WahaSession $session, ChannelSearchByViewData $request): ChannelListResultData
    {
        $data = $this->send('post', "/api/{$this->session($session)}/channels/search/by-view", $request->toArray(), 'Communication with WAHA failed while searching channels by view.');

        return ChannelListResultData::fromArray($data);
    }

    /**
     * Search for channels by text.
     */
    public function searchChannelsByText(WahaSession $session, ChannelSearchByTextData $request): ChannelListResultData
    {
        $data = $this->send('post', "/api/{$this->session($session)}/channels/search/by-text", $request->toArray(), 'Communication with WAHA failed while searching channels by text.');

        return ChannelListResultData::fromArray($data);
    }

    /**
     * Get the list of views for channel search.
     *
     * @return ChannelViewData[]
     */
    public function getSearchViews(WahaSession $session): array
    {
        $data = $this->send('get', "/api/{$this->session($session)}/channels/search/views", [], 'Communication with WAHA failed while fetching channel search views.');

        return array_map(
            static fn (array $item) => ChannelViewData::fromArray($item),
            $data,
        );
    }

    /**
     * Get the list of countries for channel search.
     *
     * @return ChannelCountryData[]
     */
    public function getSearchCountries(WahaSession $session): array
    {
        $data = $this->send('get', "/api/{$this->session($session)}/channels/search/countries", [], 'Communication with WAHA failed while fetching channel search countries.');

        return array_map(
            static fn (array $item) => ChannelCountryData::fromArray($item),
            $data,
        );
    }

    /**
     * Get the list of categories for channel search.
     *
     * @return ChannelCategoryData[]
     */
    public function getSearchCategories(WahaSession $session): array
    {
        $data = $this->send('get', "/api/{$this->session($session)}/channels/search/categories", [], 'Communication with WAHA failed while fetching channel search categories.');

        return array_map(
            static fn (array $item) => ChannelCategoryData::fromArray($item),
            $data,
        );
    }
}
