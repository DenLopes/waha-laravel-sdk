<?php

declare(strict_types=1);

namespace DenLopes\Waha\Services;

use DenLopes\Waha\Concerns\SendsWahaRequests;
use DenLopes\Waha\Data\Input\CreateGroupRequestData;
use DenLopes\Waha\Data\Input\DescriptionRequestData;
use DenLopes\Waha\Data\Input\JoinGroupRequestData;
use DenLopes\Waha\Data\Input\ParticipantsRequestData;
use DenLopes\Waha\Data\Input\ProfilePictureRequestData;
use DenLopes\Waha\Data\Input\SubjectRequestData;
use DenLopes\Waha\Data\Output\ChatPictureData;
use DenLopes\Waha\Data\Output\CountResponseData;
use DenLopes\Waha\Data\Output\GroupInfoData;
use DenLopes\Waha\Data\Output\GroupJoinInfoData;
use DenLopes\Waha\Data\Output\GroupParticipantData;
use DenLopes\Waha\Data\Output\JoinGroupData;
use DenLopes\Waha\Data\Output\ResultData;
use DenLopes\Waha\Data\SettingsMemberAddModeData;
use DenLopes\Waha\Data\SettingsSecurityChangeInfoData;
use DenLopes\Waha\Enums\WahaGroupSortFieldEnum;
use DenLopes\Waha\Enums\WahaSortOrderEnum;
use DenLopes\Waha\Support\WahaSession;

class GroupsService
{
    use SendsWahaRequests;

    /**
     * Create a group.
     */
    public function createGroup(WahaSession $session, CreateGroupRequestData $request): array
    {
        return $this->send('post', '/api/{session}/groups', $request->toArray(), 'Communication with WAHA failed while creating the group.', session: $session);
    }

    /**
     * Get all groups.
     *
     * @return GroupInfoData[]
     */
    public function getGroups(
        WahaSession $session,
        ?WahaGroupSortFieldEnum $sortBy = null,
        ?WahaSortOrderEnum $sortOrder = null,
        ?int $limit = null,
        ?int $offset = null,
        ?array $exclude = null,
    ): array {
        $payload = [];

        if ($sortBy !== null) {
            $payload['sortBy'] = $sortBy->value;
        }

        if ($sortOrder !== null) {
            $payload['sortOrder'] = $sortOrder->value;
        }

        if ($limit !== null) {
            $payload['limit'] = $limit;
        }

        if ($offset !== null) {
            $payload['offset'] = $offset;
        }

        if ($exclude !== null) {
            $payload['exclude'] = $exclude;
        }

        $data = $this->send('get', '/api/{session}/groups', $payload, 'Communication with WAHA failed while fetching groups.', session: $session);

        return array_map(
            static fn (array $group) => GroupInfoData::fromArray($group),
            $data,
        );
    }

    /**
     * Get a single group.
     */
    public function getGroup(WahaSession $session, string $id): GroupInfoData
    {
        $data = $this->send('get', "/api/{session}/groups/{$id}", [], 'Communication with WAHA failed while fetching the group.', session: $session);

        return GroupInfoData::fromArray($data);
    }

    /**
     * Delete a group.
     */
    public function deleteGroup(WahaSession $session, string $id): array
    {
        return $this->send('delete', "/api/{session}/groups/{$id}", [], 'Communication with WAHA failed while deleting the group.', session: $session);
    }

    /**
     * Leave a group.
     */
    public function leaveGroup(WahaSession $session, string $id): array
    {
        return $this->send('post', "/api/{session}/groups/{$id}/leave", [], 'Communication with WAHA failed while leaving the group.', session: $session);
    }

    /**
     * Get group info before joining via code or invite URL.
     */
    public function getJoinInfo(WahaSession $session, string $code): GroupJoinInfoData
    {
        $data = $this->send('get', '/api/{session}/groups/join-info', [
            'code' => $code,
        ], 'Communication with WAHA failed while fetching the group join info.', session: $session);

        return GroupJoinInfoData::fromArray($data);
    }

    /**
     * Join a group via code or invite URL.
     */
    public function joinGroup(WahaSession $session, JoinGroupRequestData $request): JoinGroupData
    {
        $data = $this->send('post', '/api/{session}/groups/join', $request->toArray(), 'Communication with WAHA failed while joining the group.', session: $session);

        return JoinGroupData::fromArray($data);
    }

    /**
     * Get group participants.
     *
     * @return GroupParticipantData[]
     */
    public function getGroupParticipants(WahaSession $session, string $id): array
    {
        $data = $this->send('get', "/api/{session}/groups/{$id}/participants/v2", [], 'Communication with WAHA failed while fetching the group participants.', session: $session);

        return array_map(
            static fn (array $item) => GroupParticipantData::fromArray($item),
            $data,
        );
    }

    /**
     * Add participants to a group.
     */
    public function addParticipants(WahaSession $session, string $id, ParticipantsRequestData $request): array
    {
        return $this->send('post', "/api/{session}/groups/{$id}/participants/add", $request->toArray(), 'Communication with WAHA failed while adding group participants.', session: $session);
    }

    /**
     * Remove participants from a group.
     */
    public function removeParticipants(WahaSession $session, string $id, ParticipantsRequestData $request): array
    {
        return $this->send('post', "/api/{session}/groups/{$id}/participants/remove", $request->toArray(), 'Communication with WAHA failed while removing group participants.', session: $session);
    }

    /**
     * Promote participants to admin.
     */
    public function promoteToAdmin(WahaSession $session, string $id, ParticipantsRequestData $request): array
    {
        return $this->send('post', "/api/{session}/groups/{$id}/admin/promote", $request->toArray(), 'Communication with WAHA failed while promoting group participants.', session: $session);
    }

    /**
     * Demote participants to regular users.
     */
    public function demoteToAdmin(WahaSession $session, string $id, ParticipantsRequestData $request): array
    {
        return $this->send('post', "/api/{session}/groups/{$id}/admin/demote", $request->toArray(), 'Communication with WAHA failed while demoting group participants.', session: $session);
    }

    /**
     * Get a group picture.
     */
    public function getGroupPicture(WahaSession $session, string $id, bool $refresh = false): ChatPictureData
    {
        $data = $this->send('get', "/api/{session}/groups/{$id}/picture", [
            'refresh' => $refresh,
        ], 'Communication with WAHA failed while fetching the group picture.', session: $session);

        return ChatPictureData::fromArray($data);
    }

    /**
     * Set a group picture.
     */
    public function setGroupPicture(WahaSession $session, string $id, ProfilePictureRequestData $request): ResultData
    {
        $data = $this->send('put', "/api/{session}/groups/{$id}/picture", $request->toArray(), 'Communication with WAHA failed while setting the group picture.', session: $session);

        return ResultData::fromArray($data);
    }

    /**
     * Delete a group picture.
     */
    public function deleteGroupPicture(WahaSession $session, string $id): ResultData
    {
        $data = $this->send('delete', "/api/{session}/groups/{$id}/picture", [], 'Communication with WAHA failed while deleting the group picture.', session: $session);

        return ResultData::fromArray($data);
    }

    /**
     * Update a group description.
     */
    public function setGroupDescription(WahaSession $session, string $id, DescriptionRequestData $request): array
    {
        return $this->send('put', "/api/{session}/groups/{$id}/description", $request->toArray(), 'Communication with WAHA failed while setting the group description.', session: $session);
    }

    /**
     * Update a group subject.
     */
    public function setGroupSubject(WahaSession $session, string $id, SubjectRequestData $request): array
    {
        return $this->send('put', "/api/{session}/groups/{$id}/subject", $request->toArray(), 'Communication with WAHA failed while setting the group subject.', session: $session);
    }

    /**
     * Get a group invite code.
     */
    public function getInviteCode(WahaSession $session, string $id): string
    {
        return (string) $this->send('get', "/api/{session}/groups/{$id}/invite-code", [], 'Communication with WAHA failed while fetching the group invite code.', session: $session);
    }

    /**
     * Revoke the current group invite code and generate a new one.
     */
    public function revokeInviteCode(WahaSession $session, string $id): string
    {
        return (string) $this->send('post', "/api/{session}/groups/{$id}/invite-code/revoke", [], 'Communication with WAHA failed while revoking the group invite code.', session: $session);
    }

    /**
     * Get the number of groups.
     */
    public function getGroupsCount(WahaSession $session): CountResponseData
    {
        $data = $this->send('get', '/api/{session}/groups/count', [], 'Communication with WAHA failed while counting groups.', session: $session);

        return CountResponseData::fromArray($data);
    }

    /**
     * Refresh groups from the server.
     */
    public function refreshGroups(WahaSession $session): array
    {
        return $this->send('post', '/api/{session}/groups/refresh', [], 'Communication with WAHA failed while refreshing groups.', session: $session);
    }

    /**
     * Get the legacy (non-v2) participants list.
     */
    public function getParticipants(WahaSession $session, string $id): array
    {
        return $this->send('get', "/api/{session}/groups/{$id}/participants", [], 'Communication with WAHA failed while fetching the group participants.', session: $session);
    }

    /**
     * Allow only admins to edit group info (title, description, photo).
     */
    public function setInfoAdminOnly(WahaSession $session, string $id, SettingsSecurityChangeInfoData $settings): array
    {
        return $this->send('put', "/api/{session}/groups/{$id}/settings/security/info-admin-only", $settings->toArray(), 'Communication with WAHA failed while setting the group info admin only.', session: $session);
    }

    public function getInfoAdminOnly(WahaSession $session, string $id): SettingsSecurityChangeInfoData
    {
        $data = $this->send('get', "/api/{session}/groups/{$id}/settings/security/info-admin-only", [], 'Communication with WAHA failed while fetching the group info admin only setting.', session: $session);

        return SettingsSecurityChangeInfoData::fromArray($data);
    }

    public function setMessagesAdminOnly(WahaSession $session, string $id, SettingsSecurityChangeInfoData $settings): array
    {
        return $this->send('put', "/api/{session}/groups/{$id}/settings/security/messages-admin-only", $settings->toArray(), 'Communication with WAHA failed while setting the group messages admin only.', session: $session);
    }

    public function getMessagesAdminOnly(WahaSession $session, string $id): SettingsSecurityChangeInfoData
    {
        $data = $this->send('get', "/api/{session}/groups/{$id}/settings/security/messages-admin-only", [], 'Communication with WAHA failed while fetching the group messages admin only setting.', session: $session);

        return SettingsSecurityChangeInfoData::fromArray($data);
    }

    public function setMemberAddMode(WahaSession $session, string $id, SettingsMemberAddModeData $settings): array
    {
        return $this->send('put', "/api/{session}/groups/{$id}/settings/security/member-add-mode", $settings->toArray(), 'Communication with WAHA failed while setting the group member add mode.', session: $session);
    }

    public function getMemberAddMode(WahaSession $session, string $id): SettingsMemberAddModeData
    {
        $data = $this->send('get', "/api/{session}/groups/{$id}/settings/security/member-add-mode", [], 'Communication with WAHA failed while fetching the group member add mode.', session: $session);

        return SettingsMemberAddModeData::fromArray($data);
    }
}
