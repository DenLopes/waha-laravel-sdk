<?php

declare(strict_types=1);

namespace DenLopes\Waha\Enums;

enum ChatSortField: string
{
    case CONVERSATION_TIMESTAMP = 'conversationTimestamp';
    case ID = 'id';
    case NAME = 'name';
}
