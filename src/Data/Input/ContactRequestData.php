<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;
use DenLopes\Waha\Support\WahaSession;

/**
 * Payload for blocking or unblocking a contact.
 */
final readonly class ContactRequestData extends WahaData
{
    public function __construct(
        public string $contactId,
        public WahaSession $session,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            contactId: (string) ($data['contactId'] ?? ''),
            session: WahaSession::from((string) ($data['session'] ?? '')),
        );
    }
}
