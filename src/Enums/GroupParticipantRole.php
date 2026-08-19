<?php

declare(strict_types=1);

namespace DenLopes\Waha\Enums;

enum GroupParticipantRole: string
{
    case LEFT = 'left';
    case PARTICIPANT = 'participant';
    case ADMIN = 'admin';
    case SUPERADMIN = 'superadmin';
}
