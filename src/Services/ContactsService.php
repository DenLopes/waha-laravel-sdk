<?php

declare(strict_types=1);

namespace DenLopes\Waha\Services;

use DenLopes\Waha\Concerns\SendsRequests;
use DenLopes\Waha\Data\Input\ContactRequest;
use DenLopes\Waha\Data\Input\ContactUpdateBody;
use DenLopes\Waha\Data\Output\ContactInfo;
use DenLopes\Waha\Data\Output\NumberExistResult;
use DenLopes\Waha\Data\Output\Result;
use DenLopes\Waha\Enums\ContactSortField;
use DenLopes\Waha\Enums\SortOrder;
use DenLopes\Waha\Session;

class ContactsService
{
    use SendsRequests;

    /**
     * Get all contacts.
     *
     * @return ContactInfo[]
     */
    public function getAllContacts(
        ?Session $session = null,
        ?ContactSortField $sortBy = null,
        ?SortOrder $sortOrder = null,
        ?int $limit = null,
        ?int $offset = null,
    ): array {
        $payload = ['session' => $this->session($session)];

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

        $data = $this->send('get', '/api/contacts/all', $payload, 'Communication with WAHA failed while fetching contacts.');

        return array_map(
            static fn (array $item) => ContactInfo::fromArray($item),
            $data,
        );
    }

    /**
     * Get basic contact info.
     */
    public function getContact(string $contactId, ?Session $session = null): ContactInfo
    {
        $data = $this->send('get', '/api/contacts', [
            'contactId' => $contactId,
            'session'   => $this->session($session),
        ], 'Communication with WAHA failed while fetching the contact.');

        return ContactInfo::fromArray($data);
    }

    /**
     * Get the contact's "about" info (returns null if not readable).
     */
    public function getContactAbout(string $contactId, ?Session $session = null): ?string
    {
        $data = $this->send('get', '/api/contacts/about', [
            'contactId' => $contactId,
            'session'   => $this->session($session),
        ], 'Communication with WAHA failed while fetching the contact about.');

        return is_string($data) ? $data : null;
    }

    /**
     * Get the contact's profile picture URL (returns null if not readable).
     */
    public function getContactProfilePicture(
        string $contactId,
        ?Session $session = null,
        bool $refresh = false,
    ): ?string {
        $data = $this->send('get', '/api/contacts/profile-picture', [
            'contactId' => $contactId,
            'session'   => $this->session($session),
            'refresh'   => $refresh,
        ], 'Communication with WAHA failed while fetching the contact profile picture.');

        return is_string($data) ? $data : null;
    }

    /**
     * Check whether a phone number is registered in WhatsApp.
     */
    public function checkExists(string $phone, ?Session $session = null): NumberExistResult
    {
        $data = $this->send('get', '/api/contacts/check-exists', [
            'phone'   => $phone,
            'session' => $this->session($session),
        ], 'Communication with WAHA failed while checking the number.');

        return NumberExistResult::fromArray($data);
    }

    /**
     * Block a contact.
     */
    public function blockContact(ContactRequest $request): array
    {
        return $this->send('post', '/api/contacts/block', $request->toArray(), 'Communication with WAHA failed while blocking the contact.');
    }

    /**
     * Unblock a contact.
     */
    public function unblockContact(ContactRequest $request): array
    {
        return $this->send('post', '/api/contacts/unblock', $request->toArray(), 'Communication with WAHA failed while unblocking the contact.');
    }

    /**
     * Get basic contact info for a session.
     */
    public function getContactBySession(Session $session, string $id): ContactInfo
    {
        $data = $this->send('get', "/api/{session}/contacts/{$id}", [], 'Communication with WAHA failed while fetching the session contact.', session: $session);

        return ContactInfo::fromArray($data);
    }

    /**
     * Create or update a contact on the phone address book.
     */
    public function upsertContact(Session $session, string $chatId, ContactUpdateBody $body): Result
    {
        $data = $this->send('put', "/api/{session}/contacts/{$chatId}", $body->toArray(), 'Communication with WAHA failed while upserting the contact.', session: $session);

        return Result::fromArray($data);
    }
}
