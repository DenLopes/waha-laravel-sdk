<?php

declare(strict_types=1);

namespace DenLopes\Waha\Services;

use DenLopes\Waha\Concerns\SendsRequests;
use DenLopes\Waha\Data\Input\CreateGroupRequest;
use DenLopes\Waha\Data\Input\DescriptionRequest;
use DenLopes\Waha\Data\Input\JoinGroupRequest;
use DenLopes\Waha\Data\Input\ParticipantsRequest;
use DenLopes\Waha\Data\Input\ProfilePictureRequest;
use DenLopes\Waha\Data\Input\SubjectRequest;
use DenLopes\Waha\Data\Output\ChatPicture;
use DenLopes\Waha\Data\Output\CountResponse;
use DenLopes\Waha\Data\Output\GroupInfo;
use DenLopes\Waha\Data\Output\GroupJoinInfo;
use DenLopes\Waha\Data\Output\GroupParticipant;
use DenLopes\Waha\Data\Output\JoinGroup;
use DenLopes\Waha\Data\Output\Result;
use DenLopes\Waha\Data\SettingsMemberAddMode;
use DenLopes\Waha\Data\SettingsSecurityChangeInfo;
use DenLopes\Waha\Enums\GroupSortField;
use DenLopes\Waha\Enums\SortOrder;
use DenLopes\Waha\Session;

class GroupsService
{
    use SendsRequests;

    /**
     * Create a group.
     */
    public function createGroup(Session $session, CreateGroupRequest $request): array
    {
        return $this->send('post', '/api/{session}/groups', $request->toArray(), 'Communication with WAHA failed while creating the group.', session: $session);
    }

    /**
     * Get all groups.
     *
     * @return GroupInfo[]
     */
    public function getGroups(
        Session $session,
        ?GroupSortField $sortBy = null,
        ?SortOrder $sortOrder = null,
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
            static fn (array $group) => GroupInfo::fromArray($group),
            $data,
        );
    }

    /**
     * Get a single group.
     */
    public function getGroup(Session $session, string $id): GroupInfo
    {
        $data = $this->send('get', "/api/{session}/groups/{$id}", [], 'Communication with WAHA failed while fetching the group.', session: $session);

        return GroupInfo::fromArray($data);
    }

    /**
     * Delete a group.
     */
    public function deleteGroup(Session $session, string $id): array
    {
        return $this->send('delete', "/api/{session}/groups/{$id}", [], 'Communication with WAHA failed while deleting the group.', session: $session);
    }

    /**
     * Leave a group.
     */
    public function leaveGroup(Session $session, string $id): array
    {
        return $this->send('post', "/api/{session}/groups/{$id}/leave", [], 'Communication with WAHA failed while leaving the group.', session: $session);
    }

    /**
     * Get group info before joining via code or invite URL.
     */
    public function getJoinInfo(Session $session, string $code): GroupJoinInfo
    {
        $data = $this->send('get', '/api/{session}/groups/join-info', [
            'code' => $code,
        ], 'Communication with WAHA failed while fetching the group join info.', session: $session);

        return GroupJoinInfo::fromArray($data);
    }

    /**
     * Join a group via code or invite URL.
     */
    public function joinGroup(Session $session, JoinGroupRequest $request): JoinGroup
    {
        $data = $this->send('post', '/api/{session}/groups/join', $request->toArray(), 'Communication with WAHA failed while joining the group.', session: $session);

        return JoinGroup::fromArray($data);
    }

    /**
     * Get group participants.
     *
     * @return GroupParticipant[]
     */
    public function getGroupParticipants(Session $session, string $id): array
    {
        $data = $this->send('get', "/api/{session}/groups/{$id}/participants/v2", [], 'Communication with WAHA failed while fetching the group participants.', session: $session);

        return array_map(
            static fn (array $item) => GroupParticipant::fromArray($item),
            $data,
        );
    }

    /**
     * Add participants to a group.
     */
    public function addParticipants(Session $session, string $id, ParticipantsRequest $request): array
    {
        return $this->send('post', "/api/{session}/groups/{$id}/participants/add", $request->toArray(), 'Communication with WAHA failed while adding group participants.', session: $session);
    }

    /**
     * Remove participants from a group.
     */
    public function removeParticipants(Session $session, string $id, ParticipantsRequest $request): array
    {
        return $this->send('post', "/api/{session}/groups/{$id}/participants/remove", $request->toArray(), 'Communication with WAHA failed while removing group participants.', session: $session);
    }

    /**
     * Promote participants to admin.
     */
    public function promoteToAdmin(Session $session, string $id, ParticipantsRequest $request): array
    {
        return $this->send('post', "/api/{session}/groups/{$id}/admin/promote", $request->toArray(), 'Communication with WAHA failed while promoting group participants.', session: $session);
    }

    /**
     * Demote participants to regular users.
     */
    public function demoteToAdmin(Session $session, string $id, ParticipantsRequest $request): array
    {
        return $this->send('post', "/api/{session}/groups/{$id}/admin/demote", $request->toArray(), 'Communication with WAHA failed while demoting group participants.', session: $session);
    }

    /**
     * Get a group picture.
     */
    public function getGroupPicture(Session $session, string $id, bool $refresh = false): ChatPicture
    {
        $data = $this->send('get', "/api/{session}/groups/{$id}/picture", [
            'refresh' => $refresh,
        ], 'Communication with WAHA failed while fetching the group picture.', session: $session);

        return ChatPicture::fromArray($data);
    }

    /**
     * Set a group picture.
     */
    public function setGroupPicture(Session $session, string $id, ProfilePictureRequest $request): Result
    {
        $data = $this->send('put', "/api/{session}/groups/{$id}/picture", $request->toArray(), 'Communication with WAHA failed while setting the group picture.', session: $session);

        return Result::fromArray($data);
    }

    /**
     * Delete a group picture.
     */
    public function deleteGroupPicture(Session $session, string $id): Result
    {
        $data = $this->send('delete', "/api/{session}/groups/{$id}/picture", [], 'Communication with WAHA failed while deleting the group picture.', session: $session);

        return Result::fromArray($data);
    }

    /**
     * Update a group description.
     */
    public function setGroupDescription(Session $session, string $id, DescriptionRequest $request): array
    {
        return $this->send('put', "/api/{session}/groups/{$id}/description", $request->toArray(), 'Communication with WAHA failed while setting the group description.', session: $session);
    }

    /**
     * Update a group subject.
     */
    public function setGroupSubject(Session $session, string $id, SubjectRequest $request): array
    {
        return $this->send('put', "/api/{session}/groups/{$id}/subject", $request->toArray(), 'Communication with WAHA failed while setting the group subject.', session: $session);
    }

    /**
     * Get a group invite code.
     */
    public function getInviteCode(Session $session, string $id): string
    {
        return (string) $this->send('get', "/api/{session}/groups/{$id}/invite-code", [], 'Communication with WAHA failed while fetching the group invite code.', session: $session);
    }

    /**
     * Revoke the current group invite code and generate a new one.
     */
    public function revokeInviteCode(Session $session, string $id): string
    {
        return (string) $this->send('post', "/api/{session}/groups/{$id}/invite-code/revoke", [], 'Communication with WAHA failed while revoking the group invite code.', session: $session);
    }

    /**
     * Get the number of groups.
     */
    public function getGroupsCount(Session $session): CountResponse
    {
        $data = $this->send('get', '/api/{session}/groups/count', [], 'Communication with WAHA failed while counting groups.', session: $session);

        return CountResponse::fromArray($data);
    }

    /**
     * Refresh groups from the server.
     */
    public function refreshGroups(Session $session): array
    {
        return $this->send('post', '/api/{session}/groups/refresh', [], 'Communication with WAHA failed while refreshing groups.', session: $session);
    }

    /**
     * Get the legacy (non-v2) participants list.
     */
    public function getParticipants(Session $session, string $id): array
    {
        return $this->send('get', "/api/{session}/groups/{$id}/participants", [], 'Communication with WAHA failed while fetching the group participants.', session: $session);
    }

    /**
     * Allow only admins to edit group info (title, description, photo).
     */
    public function setInfoAdminOnly(Session $session, string $id, SettingsSecurityChangeInfo $settings): array
    {
        return $this->send('put', "/api/{session}/groups/{$id}/settings/security/info-admin-only", $settings->toArray(), 'Communication with WAHA failed while setting the group info admin only.', session: $session);
    }

    public function getInfoAdminOnly(Session $session, string $id): SettingsSecurityChangeInfo
    {
        $data = $this->send('get', "/api/{session}/groups/{$id}/settings/security/info-admin-only", [], 'Communication with WAHA failed while fetching the group info admin only setting.', session: $session);

        return SettingsSecurityChangeInfo::fromArray($data);
    }

    public function setMessagesAdminOnly(Session $session, string $id, SettingsSecurityChangeInfo $settings): array
    {
        return $this->send('put', "/api/{session}/groups/{$id}/settings/security/messages-admin-only", $settings->toArray(), 'Communication with WAHA failed while setting the group messages admin only.', session: $session);
    }

    public function getMessagesAdminOnly(Session $session, string $id): SettingsSecurityChangeInfo
    {
        $data = $this->send('get', "/api/{session}/groups/{$id}/settings/security/messages-admin-only", [], 'Communication with WAHA failed while fetching the group messages admin only setting.', session: $session);

        return SettingsSecurityChangeInfo::fromArray($data);
    }

    public function setMemberAddMode(Session $session, string $id, SettingsMemberAddMode $settings): array
    {
        return $this->send('put', "/api/{session}/groups/{$id}/settings/security/member-add-mode", $settings->toArray(), 'Communication with WAHA failed while setting the group member add mode.', session: $session);
    }

    public function getMemberAddMode(Session $session, string $id): SettingsMemberAddMode
    {
        $data = $this->send('get', "/api/{session}/groups/{$id}/settings/security/member-add-mode", [], 'Communication with WAHA failed while fetching the group member add mode.', session: $session);

        return SettingsMemberAddMode::fromArray($data);
    }
}
