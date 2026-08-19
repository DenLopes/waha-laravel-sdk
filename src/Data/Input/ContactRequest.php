<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;
use DenLopes\Waha\Session;

/**
 * Payload for blocking or unblocking a contact.
 */
final readonly class ContactRequest extends Data
{
    public function __construct(
        public string $contactId,
        public Session $session,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            contactId: (string) ($data['contactId'] ?? ''),
            session: Session::from((string) ($data['session'] ?? '')),
        );
    }
}
