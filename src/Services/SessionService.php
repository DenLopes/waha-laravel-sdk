<?php

declare(strict_types=1);

namespace DenLopes\Waha\Services;

use DenLopes\Waha\Concerns\SendsWahaRequests;
use DenLopes\Waha\Data\Input\SessionCreateRequestData;
use DenLopes\Waha\Data\Input\SessionUpdateRequestData;
use DenLopes\Waha\Data\Output\MeInfoData;
use DenLopes\Waha\Data\Output\MessageCappingData;
use DenLopes\Waha\Data\Output\ReachoutTimelockData;
use DenLopes\Waha\Data\Output\SessionData;
use DenLopes\Waha\Data\Output\SessionInfoData;
use DenLopes\Waha\Support\WahaSession;

class SessionService
{
    use SendsWahaRequests;

    /**
     * List all sessions.
     *
     * @param  string[]|null  $expand  Expand additional session details (e.g. ["apps"]).
     * @return SessionInfoData[]
     */
    public function listSessions(bool $all = false, ?array $expand = null): array
    {
        $payload = [];

        if ($all) {
            $payload['all'] = true;
        }

        if ($expand !== null) {
            $payload['expand'] = $expand;
        }

        $data = $this->send('get', '/api/sessions', $payload, 'Communication with WAHA failed while listing sessions.');

        return array_map(
            static fn (array $item) => SessionInfoData::fromArray($item),
            $data,
        );
    }

    /**
     * Create (and optionally start) a session.
     */
    public function createSession(SessionCreateRequestData $request): SessionData
    {
        $data = $this->send('post', '/api/sessions', $request->toArray(), 'Communication with WAHA failed while creating the session.');

        return SessionData::fromArray($data);
    }

    /**
     * Get a single session.
     *
     * @param  string[]|null  $expand  Expand additional session details (e.g. ["apps"]).
     */
    public function getSession(WahaSession $session, ?array $expand = null): SessionInfoData
    {
        $payload = [];

        if ($expand !== null) {
            $payload['expand'] = $expand;
        }

        $data = $this->send('get', "/api/sessions/{$this->session($session)}", $payload, 'Communication with WAHA failed while fetching the session.');

        return SessionInfoData::fromArray($data);
    }

    /**
     * Update a session config and/or apps.
     */
    public function updateSession(WahaSession $session, SessionUpdateRequestData $request): SessionData
    {
        $data = $this->send('put', "/api/sessions/{$this->session($session)}", $request->toArray(), 'Communication with WAHA failed while updating the session.');

        return SessionData::fromArray($data);
    }

    /**
     * Delete a session (stop and logout as well). Idempotent.
     */
    public function deleteSession(WahaSession $session): array
    {
        return $this->send('delete', "/api/sessions/{$this->session($session)}", [], 'Communication with WAHA failed while deleting the session.');
    }

    /**
     * Get information about the authenticated account.
     */
    public function getMe(WahaSession $session): MeInfoData
    {
        $data = $this->send('get', "/api/sessions/{$this->session($session)}/me", [], 'Communication with WAHA failed while fetching the authenticated account.');

        return MeInfoData::fromArray($data);
    }

    /**
     * Fetch the account new-chat message capping (per-cycle quota).
     */
    public function fetchMessageCapping(WahaSession $session): MessageCappingData
    {
        $data = $this->send('get', "/api/sessions/{$this->session($session)}/capping", [], 'Communication with WAHA failed while fetching the message capping.');

        return MessageCappingData::fromArray($data);
    }

    /**
     * Fetch the account reachout timelock state.
     */
    public function fetchReachoutTimelock(WahaSession $session): ReachoutTimelockData
    {
        $data = $this->send('get', "/api/sessions/{$this->session($session)}/timelock", [], 'Communication with WAHA failed while fetching the reachout timelock.');

        return ReachoutTimelockData::fromArray($data);
    }

    /**
     * Start a session. Idempotent.
     */
    public function startSession(WahaSession $session): SessionData
    {
        $data = $this->send('post', "/api/sessions/{$this->session($session)}/start", [], 'Communication with WAHA failed while starting the session.');

        return SessionData::fromArray($data);
    }

    /**
     * Stop a session. Idempotent.
     */
    public function stopSession(WahaSession $session): SessionData
    {
        $data = $this->send('post', "/api/sessions/{$this->session($session)}/stop", [], 'Communication with WAHA failed while stopping the session.');

        return SessionData::fromArray($data);
    }

    /**
     * Logout from a session.
     */
    public function logoutSession(WahaSession $session): SessionData
    {
        $data = $this->send('post', "/api/sessions/{$this->session($session)}/logout", [], 'Communication with WAHA failed while logging out of the session.');

        return SessionData::fromArray($data);
    }

    /**
     * Restart a session.
     */
    public function restartSession(WahaSession $session): SessionData
    {
        $data = $this->send('post', "/api/sessions/{$this->session($session)}/restart", [], 'Communication with WAHA failed while restarting the session.');

        return SessionData::fromArray($data);
    }

    /**
     * Upsert and start a session (deprecated WAHA endpoint).
     *
     * @param  array|null  $config  Session config.
     */
    public function upsertAndStartSession(string $name, ?array $config = null): SessionData
    {
        $payload = ['name' => $name];

        if ($config !== null) {
            $payload['config'] = $config;
        }

        $data = $this->send('post', '/api/sessions/start', $payload, 'Communication with WAHA failed while starting the session.');

        return SessionData::fromArray($data);
    }

    /**
     * Stop (and optionally logout from) a session (deprecated WAHA endpoint).
     */
    public function stopSessionDeprecated(string $name, bool $logout = false): array
    {
        return $this->send('post', '/api/sessions/stop', [
            'name'   => $name,
            'logout' => $logout,
        ], 'Communication with WAHA failed while stopping the session.');
    }

    /**
     * Stop, logout and delete a session (deprecated WAHA endpoint).
     */
    public function logoutAndDeleteSession(string $name): array
    {
        return $this->send('post', '/api/sessions/logout', [
            'name' => $name,
        ], 'Communication with WAHA failed while logging out of the session.');
    }
}
