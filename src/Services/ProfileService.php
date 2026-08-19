<?php

declare(strict_types=1);

namespace DenLopes\Waha\Services;

use DenLopes\Waha\Concerns\SendsRequests;
use DenLopes\Waha\Data\Input\ProfileNameRequest;
use DenLopes\Waha\Data\Input\ProfilePictureRequest;
use DenLopes\Waha\Data\Input\ProfileStatusRequest;
use DenLopes\Waha\Data\Output\MyProfile;
use DenLopes\Waha\Data\Output\Result;
use DenLopes\Waha\Session;

class ProfileService
{
    use SendsRequests;

    /**
     * Get my WhatsApp profile.
     */
    public function getMyProfile(Session $session): MyProfile
    {
        $data = $this->send('get', '/api/{session}/profile', [], 'Communication with WAHA failed while fetching the profile.', session: $session);

        return MyProfile::fromArray($data);
    }

    /**
     * Set my profile name.
     */
    public function setProfileName(Session $session, ProfileNameRequest $request): Result
    {
        $data = $this->send('put', '/api/{session}/profile/name', $request->toArray(), 'Communication with WAHA failed while setting the profile name.', session: $session);

        return Result::fromArray($data);
    }

    /**
     * Set my profile status (About).
     */
    public function setProfileStatus(Session $session, ProfileStatusRequest $request): Result
    {
        $data = $this->send('put', '/api/{session}/profile/status', $request->toArray(), 'Communication with WAHA failed while setting the profile status.', session: $session);

        return Result::fromArray($data);
    }

    /**
     * Set my profile picture.
     */
    public function setProfilePicture(Session $session, ProfilePictureRequest $request): Result
    {
        $data = $this->send('put', '/api/{session}/profile/picture', $request->toArray(), 'Communication with WAHA failed while setting the profile picture.', session: $session);

        return Result::fromArray($data);
    }

    /**
     * Delete my profile picture.
     */
    public function deleteProfilePicture(Session $session): Result
    {
        $data = $this->send('delete', '/api/{session}/profile/picture', [], 'Communication with WAHA failed while deleting the profile picture.', session: $session);

        return Result::fromArray($data);
    }
}
