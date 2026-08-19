<?php

declare(strict_types=1);

namespace DenLopes\Waha\Facades;

use DenLopes\Waha\Client;
use DenLopes\Waha\Resources\Chat;
use DenLopes\Waha\Resources\Conversation;
use DenLopes\Waha\Resources\Message;
use DenLopes\Waha\Session;
use DenLopes\Waha\Support\Pacing;
use Illuminate\Support\Facades\Facade;

/**
 * Static facade for the WAHA SDK.
 *
 * @method static Chat chat(string $chatId, string|Session|null $session = null)
 * @method static Message message(string $chatId, string $id, string|Session|null $session = null)
 * @method static Conversation conversation(string $chatId, string|Session|null $session = null, ?Pacing $policy = null)
 * @method static Session session(string|Session|null $session = null)
 *
 * @see Client
 */
class Waha extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Client::class;
    }
}
