<?php

declare(strict_types=1);

namespace DenLopes\Waha\Enums;

/**
 * Where a contact sits in the relationship lifecycle.
 *
 * The stage drives which anti-abuse tier applies to a send:
 *
 *   - Cold: no prior inbound signal, highest risk, tightest quotas.
 *   - Warm: the contact has messaged us before, relaxed quotas.
 *   - Reply: we are answering a specific inbound message, loosest quotas.
 */
enum ContactStage: string
{
    case Cold = 'cold';
    case Warm = 'warm';
    case Reply = 'reply';
}
