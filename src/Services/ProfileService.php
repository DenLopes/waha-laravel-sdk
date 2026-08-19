<?php

declare(strict_types=1);

namespace DenLopes\Waha\Services;

use DenLopes\Waha\Concerns\SendsWahaRequests;
use DenLopes\Waha\Data\Input\ProfileNameRequestData;
use DenLopes\Waha\Data\Input\ProfilePictureRequestData;
use DenLopes\Waha\Data\Input\ProfileStatusRequestData;
use DenLopes\Waha\Data\Output\MyProfileData;
use DenLopes\Waha\Data\Output\ResultData;
use DenLopes\Waha\Support\WahaSession;

class ProfileService
{
    use SendsWahaRequests;

    /**
     * Get my WhatsApp profile.
     */
    public function getMyProfile(WahaSession $session): MyProfileData
    {
        $data = $this->send('get', '/api/{session}/profile', [], 'Communication with WAHA failed while fetching the profile.', session: $session);

        return MyProfileData::fromArray($data);
    }

    /**
     * Set my profile name.
     */
    public function setProfileName(WahaSession $session, ProfileNameRequestData $request): ResultData
    {
        $data = $this->send('put', '/api/{session}/profile/name', $request->toArray(), 'Communication with WAHA failed while setting the profile name.', session: $session);

        return ResultData::fromArray($data);
    }

    /**
     * Set my profile status (About).
     */
    public function setProfileStatus(WahaSession $session, ProfileStatusRequestData $request): ResultData
    {
        $data = $this->send('put', '/api/{session}/profile/status', $request->toArray(), 'Communication with WAHA failed while setting the profile status.', session: $session);

        return ResultData::fromArray($data);
    }

    /**
     * Set my profile picture.
     */
    public function setProfilePicture(WahaSession $session, ProfilePictureRequestData $request): ResultData
    {
        $data = $this->send('put', '/api/{session}/profile/picture', $request->toArray(), 'Communication with WAHA failed while setting the profile picture.', session: $session);

        return ResultData::fromArray($data);
    }

    /**
     * Delete my profile picture.
     */
    public function deleteProfilePicture(WahaSession $session): ResultData
    {
        $data = $this->send('delete', '/api/{session}/profile/picture', [], 'Communication with WAHA failed while deleting the profile picture.', session: $session);

        return ResultData::fromArray($data);
    }
}
