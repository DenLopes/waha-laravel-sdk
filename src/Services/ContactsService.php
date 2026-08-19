<?php

declare(strict_types=1);

namespace DenLopes\Waha\Services;

use DenLopes\Waha\Concerns\SendsWahaRequests;
use DenLopes\Waha\Data\Input\ContactRequestData;
use DenLopes\Waha\Data\Input\ContactUpdateBodyData;
use DenLopes\Waha\Data\Output\ContactInfoData;
use DenLopes\Waha\Data\Output\ResultData;
use DenLopes\Waha\Data\Output\WANumberExistResultData;
use DenLopes\Waha\Enums\WahaContactSortFieldEnum;
use DenLopes\Waha\Enums\WahaSortOrderEnum;
use DenLopes\Waha\Support\WahaSession;

class ContactsService
{
    use SendsWahaRequests;

    /**
     * Get all contacts.
     *
     * @return ContactInfoData[]
     */
    public function getAllContacts(
        ?WahaSession $session = null,
        ?WahaContactSortFieldEnum $sortBy = null,
        ?WahaSortOrderEnum $sortOrder = null,
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
            static fn (array $item) => ContactInfoData::fromArray($item),
            $data,
        );
    }

    /**
     * Get basic contact info.
     */
    public function getContact(string $contactId, ?WahaSession $session = null): ContactInfoData
    {
        $data = $this->send('get', '/api/contacts', [
            'contactId' => $contactId,
            'session'   => $this->session($session),
        ], 'Communication with WAHA failed while fetching the contact.');

        return ContactInfoData::fromArray($data);
    }

    /**
     * Get the contact's "about" info (returns null if not readable).
     */
    public function getContactAbout(string $contactId, ?WahaSession $session = null): ?string
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
        ?WahaSession $session = null,
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
    public function checkExists(string $phone, ?WahaSession $session = null): WANumberExistResultData
    {
        $data = $this->send('get', '/api/contacts/check-exists', [
            'phone'   => $phone,
            'session' => $this->session($session),
        ], 'Communication with WAHA failed while checking the number.');

        return WANumberExistResultData::fromArray($data);
    }

    /**
     * Block a contact.
     */
    public function blockContact(ContactRequestData $request): array
    {
        return $this->send('post', '/api/contacts/block', $request->toArray(), 'Communication with WAHA failed while blocking the contact.');
    }

    /**
     * Unblock a contact.
     */
    public function unblockContact(ContactRequestData $request): array
    {
        return $this->send('post', '/api/contacts/unblock', $request->toArray(), 'Communication with WAHA failed while unblocking the contact.');
    }

    /**
     * Get basic contact info for a session.
     */
    public function getContactBySession(WahaSession $session, string $id): ContactInfoData
    {
        $data = $this->send('get', "/api/{session}/contacts/{$id}", [], 'Communication with WAHA failed while fetching the session contact.', session: $session);

        return ContactInfoData::fromArray($data);
    }

    /**
     * Create or update a contact on the phone address book.
     */
    public function upsertContact(WahaSession $session, string $chatId, ContactUpdateBodyData $body): ResultData
    {
        $data = $this->send('put', "/api/{session}/contacts/{$chatId}", $body->toArray(), 'Communication with WAHA failed while upserting the contact.', session: $session);

        return ResultData::fromArray($data);
    }
}
