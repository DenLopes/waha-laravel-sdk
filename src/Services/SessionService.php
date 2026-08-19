<?php

declare(strict_types=1);

namespace DenLopes\Waha\Services;

use DenLopes\Waha\Concerns\SendsRequests;
use DenLopes\Waha\Data\Input\SessionCreateRequest;
use DenLopes\Waha\Data\Input\SessionUpdateRequest;
use DenLopes\Waha\Data\Output\MeInfo;
use DenLopes\Waha\Data\Output\MessageCapping;
use DenLopes\Waha\Data\Output\ReachoutTimelock;
use DenLopes\Waha\Data\Output\SessionData;
use DenLopes\Waha\Data\Output\SessionInfo;
use DenLopes\Waha\Session;

class SessionService
{
    use SendsRequests;

    /**
     * List all sessions.
     *
     * @param  string[]|null  $expand  Expand additional session details (e.g. ["apps"]).
     * @return SessionInfo[]
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
            static fn (array $item) => SessionInfo::fromArray($item),
            $data,
        );
    }

    /**
     * Create (and optionally start) a session.
     */
    public function createSession(SessionCreateRequest $request): SessionData
    {
        $data = $this->send('post', '/api/sessions', $request->toArray(), 'Communication with WAHA failed while creating the session.');

        return SessionData::fromArray($data);
    }

    /**
     * Get a single session.
     *
     * @param  string[]|null  $expand  Expand additional session details (e.g. ["apps"]).
     */
    public function getSession(Session $session, ?array $expand = null): SessionInfo
    {
        $payload = [];

        if ($expand !== null) {
            $payload['expand'] = $expand;
        }

        $data = $this->send('get', '/api/sessions/{session}', $payload, 'Communication with WAHA failed while fetching the session.', session: $session);

        return SessionInfo::fromArray($data);
    }

    /**
     * Update a session config and/or apps.
     */
    public function updateSession(Session $session, SessionUpdateRequest $request): SessionData
    {
        $data = $this->send('put', '/api/sessions/{session}', $request->toArray(), 'Communication with WAHA failed while updating the session.', session: $session);

        return SessionData::fromArray($data);
    }

    /**
     * Delete a session (stop and logout as well). Idempotent.
     */
    public function deleteSession(Session $session): array
    {
        return $this->send('delete', '/api/sessions/{session}', [], 'Communication with WAHA failed while deleting the session.', session: $session);
    }

    /**
     * Get information about the authenticated account.
     */
    public function getMe(Session $session): MeInfo
    {
        $data = $this->send('get', '/api/sessions/{session}/me', [], 'Communication with WAHA failed while fetching the authenticated account.', session: $session);

        return MeInfo::fromArray($data);
    }

    /**
     * Fetch the account new-chat message capping (per-cycle quota).
     */
    public function fetchMessageCapping(Session $session): MessageCapping
    {
        $data = $this->send('get', '/api/sessions/{session}/capping', [], 'Communication with WAHA failed while fetching the message capping.', session: $session);

        return MessageCapping::fromArray($data);
    }

    /**
     * Fetch the account reachout timelock state.
     */
    public function fetchReachoutTimelock(Session $session): ReachoutTimelock
    {
        $data = $this->send('get', '/api/sessions/{session}/timelock', [], 'Communication with WAHA failed while fetching the reachout timelock.', session: $session);

        return ReachoutTimelock::fromArray($data);
    }

    /**
     * Start a session. Idempotent.
     */
    public function startSession(Session $session): SessionData
    {
        $data = $this->send('post', '/api/sessions/{session}/start', [], 'Communication with WAHA failed while starting the session.', session: $session);

        return SessionData::fromArray($data);
    }

    /**
     * Stop a session. Idempotent.
     */
    public function stopSession(Session $session): SessionData
    {
        $data = $this->send('post', '/api/sessions/{session}/stop', [], 'Communication with WAHA failed while stopping the session.', session: $session);

        return SessionData::fromArray($data);
    }

    /**
     * Logout from a session.
     */
    public function logoutSession(Session $session): SessionData
    {
        $data = $this->send('post', '/api/sessions/{session}/logout', [], 'Communication with WAHA failed while logging out of the session.', session: $session);

        return SessionData::fromArray($data);
    }

    /**
     * Restart a session.
     */
    public function restartSession(Session $session): SessionData
    {
        $data = $this->send('post', '/api/sessions/{session}/restart', [], 'Communication with WAHA failed while restarting the session.', session: $session);

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
