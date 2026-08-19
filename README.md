# WAHA Laravel SDK

A typed, multi-host Laravel client for [WAHA](https://waha.devlike.pro/) (WhatsApp
HTTP API). It wraps the HTTP endpoints in injectable services, maps JSON payloads
to and from DTOs, and adds a small fluent layer for chat, message, and
human-like conversation flows. Webhook verification, dispatch, and config- or
DB-backed host routing are built in.

## Table of contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Quick start](#quick-start)
- [Resources](#resources)
- [Sessions](#sessions)
- [Services](#services)
- [DTOs](#dtos)
- [Configuration](#configuration)
- [Multi-host](#multi-host)
- [Logging](#logging)
- [Webhooks](#webhooks)
- [Errors](#errors)
- [Architecture](#architecture)
- [Coverage](#coverage)
- [Testing](#testing)

## Requirements

- PHP `^8.3`
- Laravel `^10.0 || ^11.0 || ^12.0 || ^13.0`

## Installation

```bash
composer require denlopes/waha-laravel-sdk
```

Package discovery registers `DenLopes\Waha\WahaServiceProvider` and the `Waha`
facade automatically. Publish the config and migrations:

```bash
php artisan vendor:publish --tag="waha-config"
php artisan vendor:publish --tag="waha-migrations"
```

Add the WAHA connection settings to your `.env` (see
[Configuration](#configuration)).

### Local development

When working on the package alongside an application, register it as a Composer
path repository:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "packages/denlopes/waha-laravel-sdk",
            "options": { "symlink": true }
        }
    ],
    "require": {
        "denlopes/waha-laravel-sdk": "@dev"
    }
}
```

## Quick start

The facade is the fastest entry point:

```php
use Waha;

$chat = Waha::chat('5511999999999@c.us');

$message = $chat->sendMessage('Hello from Laravel');
```

If you prefer constructor injection, `app(\DenLopes\Waha\Client::class)` is the
class the facade resolves to.

## Resources

Three fluent resource handles — `Chat`, `Message`, and `Conversation` — cover the
common chat and message flows. They carry their session and ID so you don't repeat
them on every call.

### Chat

```php
use DenLopes\Waha\Data\Input\RemoteFile;
use Waha;

$chat = Waha::chat('5511999999999@c.us', 'sales'); // session name or Session object

$message = $chat->sendMessage('Hello from Laravel');

// Every send* returns a Message, so message actions chain directly.
$chat->sendImage(new RemoteFile(mimetype: 'image/jpeg', url: 'https://example.com/pic.jpg'))
    ->react('🔥');
```

Lookups:

```php
$chat->message($message->id());      // lazy handle, no I/O until get()
$chat->find($message->id());         // eager fetch
$chat->getMessages(limit: 50);       // list, as Message objects
```

Send methods — `sendMessage`, `sendImage`, `sendFile`, `sendVoice`, `sendVideo`,
`sendPoll`, `sendLocation`, `sendContactVcard`, `sendList`,
`sendLinkCustomPreview`, and `forward` — each return a `Message`.

State-changing actions return `$this` for chaining: `startTyping()`,
`stopTyping()`, `react()`, `star()`, `markRead()`, `pinMessage()`,
`unpinMessage()`, `archive()`, `unarchive()`, `markUnread()`, `clearMessages()`,
and `delete()`.

### Message

A `Message` is returned by every `send*` method and by `message()` / `find()`:

```php
$existing = $chat->message($message->id());

$existing
    ->markRead()
    ->pin()
    ->update('Updated text')
    ->delete();
```

`Message` exposes `get()` (the raw `MessageData`), `refresh()`, `markRead()`,
`react()`, `star()`, `pin()`, `unpin()`, `update()`, `forward()`, `delete()`, and
`toArray()` / `toJson()`.

### Conversations (anti-ban)

`Conversation` wraps a `Chat` and sends messages the way WAHA recommends to avoid
being flagged as spam:

```php
use Waha;

$conversation = Waha::conversation('5511999999999@c.us');

// markRead → startTyping → random typing delay → stopTyping → sendText
$message = $conversation->send('Hello from Laravel');

// Reply to an inbound message with the same flow.
$reply = $conversation->reply('Thanks for reaching out!', $incomingMessageId);
```

Or build one from an existing chat and drive the lower-level steps yourself:

```php
$conversation = $chat->conversation();

$conversation
    ->markRead()
    ->startTyping()
    ->wait(800)
    ->stopTyping();

$conversation->reset(); // clear pacing state, e.g. when a human takes over
```

The behavior is driven by the `waha.conversations` config block and represented
by the `DenLopes\Waha\Support\Pacing` value object. It simulates word-by-word
typing with an occasional pause, spaces out consecutive messages with a random
cooldown, and enforces an optional per-window message cap. When the cap is hit it
throws `ConversationThrottledException` instead of hammering the contact — catch
it and schedule a retry or pause the outreach.

```php
use DenLopes\Waha\Support\Pacing;

Pacing::fromConfig(); // reads waha.conversations
Pacing::off();        // no humanization, no pacing (useful in tests)
```

Pacing state lives on the conversation instance. Create one conversation per
contact flow and reuse it for the lifetime of that flow; for cross-process
throttling, combine it with Laravel's queue or rate limiter.

## Sessions

A session name is wrapped in a `Session` value object so it can't be confused
with a chat ID, message ID, or phone number.

```php
use DenLopes\Waha\Session;

$session = Session::from('default');
$session = Session::default(); // uses waha.default_session

$session->value(); // string
(string) $session; // string
```

Most service methods accept `?Session $session = null` and fall back to the
configured default when omitted.

## Services

Below the fluent layer, each WAHA API area has its own injectable service.
Services follow a consistent naming convention: `list*`, `get*`, `create*`,
`update*`, `delete*`, `send*`, `set*`.

```php
use DenLopes\Waha\Services\MessagingService;

$messaging = app(MessagingService::class);

$message = $messaging->sendText(
    chatId: '5511999999999@c.us',
    text: 'Hello!',
);
```

The full service list is in [Coverage](#coverage).

## DTOs

- **Request DTOs** live in `src/Data/Input` and extend `Data`. Construct them with
  named arguments and serialize them with `toArray()` / `toJson()`.
- **Response and event DTOs** live in `src/Data/Output` and are built from API
  arrays with `fromArray()` (or `fromJson()`).

The `Data` serializer walks public constructor-promoted properties, skips `null`
values (WAHA treats an omitted key as "leave unchanged"), and recursively
serializes nested DTOs, backed enums, and arrays. It also provides safe extraction
helpers — `string()`, `arrayValue()`, `intValue()`, `boolValue()` — used by
`fromArray()` mappers to degrade gracefully on unexpected payloads.

```php
use DenLopes\Waha\Data\Input\ApiKeyRequest;
use DenLopes\Waha\Data\SessionActions;

$request = new ApiKeyRequest(
    isAdmin: false,
    session: 'default',
    isActive: true,
    actions: new SessionActions(
        read: true,
        send: true,
        control: false,
        setting: false,
        app: false,
        delete: false,
    ),
);

$request->toArray();
```

## Configuration

All settings come from `config/waha.php` and the environment, read through
Laravel's `config()` helper.

| Config key             | Env var                 | Default                 | Description                                   |
| ---------------------- | ----------------------- | ----------------------- | --------------------------------------------- |
| `waha.base_url`        | `WAHA_BASE_URL`         | `http://localhost:3000` | Base URL of the WAHA server.                  |
| `waha.api_key`         | `WAHA_API_KEY`          | _(none)_                | Secret sent via the `X-Api-Key` header.       |
| `waha.default_session` | `WAHA_DEFAULT_SESSION`  | `default`               | Session used when none is given explicitly.   |
| `waha.timeout`         | `WAHA_TIMEOUT`          | `30`                    | HTTP request timeout, in seconds.             |
| `waha.connect_timeout` | `WAHA_CONNECT_TIMEOUT`  | `5`                     | TCP connection timeout, in seconds.           |
| `waha.retry_attempts`  | `WAHA_RETRY_ATTEMPTS`   | `3`                     | Retries for transient failures and connection errors on idempotent methods. |
| `waha.retry_delay_ms`  | `WAHA_RETRY_DELAY_MS`   | `200`                   | Initial retry backoff in ms (exponential, with jitter). |

```dotenv
WAHA_BASE_URL=http://localhost:3000
WAHA_API_KEY=your-secret-key
WAHA_DEFAULT_SESSION=default
WAHA_TIMEOUT=30
WAHA_CONNECT_TIMEOUT=5
WAHA_RETRY_ATTEMPTS=3
WAHA_RETRY_DELAY_MS=200
```

Multi-host and webhook settings live in their own sections below.

## Multi-host

Define `waha.hosts` to talk to more than one WAHA server. When empty, the
single-host keys above are used as the `primary` host.

```php
// config/waha.php
'default_host' => env('WAHA_DEFAULT_HOST', 'primary'),

'hosts' => [
    'primary' => [
        'base_url'        => env('WAHA_PRIMARY_URL'),
        'api_key'         => env('WAHA_PRIMARY_API_KEY'),
        'api_key_header'  => env('WAHA_API_KEY_HEADER', 'X-Api-Key'),
        'default_session' => env('WAHA_PRIMARY_DEFAULT_SESSION', 'default'),
        'mode'            => env('WAHA_PRIMARY_MODE', 'admin_fallback'),
        'session_keys'    => [],
    ],
    'secondary' => [
        'base_url' => env('WAHA_SECONDARY_URL'),
        'api_key'  => env('WAHA_SECONDARY_API_KEY'),
    ],
],
```

Host selection is abstracted behind `HostRegistry`, `ApiKeyProvider`, and
`SessionRouter` contracts. Hosts are normalized into an immutable `HostConfig`
value object, and the `mode` string is represented by the `ApiKeyMode` enum
(`ADMIN_FALLBACK` / `STRICT_SESSION_KEY`).

### DB-backed hosts

Set `WAHA_REGISTRY_DRIVER=db` to read hosts from the `waha_hosts` table instead of
config. Run `php artisan migrate`, then seed the table. Each host is keyed by a
unique `key` and can define per-session API keys.

### Session → host pinning

Set `WAHA_ROUTING_DRIVER=pin` to resolve the host from the `waha_session_pins`
table (session name → host key), falling back to `default_host` when unknown.

```php
use DenLopes\Waha\Contracts\PinStore;

$pins = app(PinStore::class);

$pins->pin('company-123', 'company-host');
$pins->getHostForSession('company-123'); // 'company-host'
$pins->forget('company-123');
```

This is how each tenant gets its own WhatsApp number — and, as it grows, its own
WAHA host — without hardcoding that mapping in the SDK.

## Logging

The package merges two dedicated channels into the host application's logging
config: `waha` (request/response lifecycle) and `wahaError` (failures). Override
them in your own `config/logging.php` if you want different drivers, paths, or
levels.

## Webhooks

When enabled (the default), the service provider registers a stateless route for
inbound WAHA deliveries. It verifies the request, parses it into a typed
`Webhook`, then dispatches it.

### Route

Default endpoint: `POST /webhooks/waha`. Configure it with
`waha.webhooks.route.prefix` (`WAHA_WEBHOOKS_ROUTE_PREFIX`) and
`waha.webhooks.route.middleware`.

### Verification

The controller checks, in order:

1. **HMAC signature** — `X-Webhook-Hmac` over the raw body using
   `waha.webhooks.secret` (`WAHA_WEBHOOK_SECRET`). The algorithm comes from
   `X-Webhook-Hmac-Algorithm` and defaults to `sha512`.
2. **Timestamp freshness** — `X-Webhook-Timestamp` against
   `waha.webhooks.max_clock_skew_ms`.
3. **Replay de-duplication** — `X-Webhook-Request-Id` via the cache for
   `waha.webhooks.replay.ttl_seconds`.

Set `WAHA_WEBHOOKS_REQUIRE_HMAC=false` to accept unauthenticated deliveries
(not recommended outside development).

### Handling

Two extension points:

- **Laravel event** — `DenLopes\Waha\Webhooks\Events\WebhookReceived` is always
  fired and carries the parsed `Webhook` plus the raw body and request ID.
- **Configured handlers** — map WAHA event names to handler classes:

```php
// config/waha.php
'webhooks' => [
    'handlers' => [
        'message.any' => \App\Waha\Handlers\MessageHandler::class,
        'message.*'   => \App\Waha\Handlers\AnyMessageHandler::class,
    ],
],
```

Handlers implement `DenLopes\Waha\Webhooks\Contracts\WebhookHandler`.

### Processing mode

- `sync` (default) — runs handlers inline during the HTTP request.
- `queue` — dispatches `ProcessWebhookJob` and returns immediately
  (`WAHA_WEBHOOKS_PROCESSING_MODE=queue`).

### Parsing

`Webhook::fromArray()` maps `payload` to the most specific DTO for the event
(e.g. `MessageData` for `message`). Unrecognized events keep their raw array.

### Storage

Set `WAHA_WEBHOOKS_STORE_ENABLED=true` to persist verified deliveries to the
`waha_webhook_events` table.

## Errors

Every failure is thrown as a subclass of `DenLopes\Waha\Exceptions\WahaException`,
so you can catch the base type for "any WAHA problem" or a specific subtype for
targeted handling. API/HTTP failures share `ApiException` as their base.

| Exception              | Trigger                                          |
| ---------------------- | ------------------------------------------------ |
| `ApiException`         | Base for API/HTTP errors                         |
| `AuthenticationException` | `401`/`403`                                   |
| `CredentialsException` | Missing/invalid API key (extends `AuthenticationException`) |
| `SessionNotFoundException` | `404` on a session-scoped endpoint          |
| `NoDataException`      | `404` on a non-session resource                  |
| `RateLimitException`   | `429`                                            |
| `RequestException`     | `400`/`422`                                      |
| `ServerException`      | `5xx`                                            |
| `ConnectionException`  | Connection failure or timeout                    |
| `IntegrationException` | JSON decode failures and unclassified failures   |
| `NotImplementedException` | `501` endpoint not implemented by the engine  |
| `UnknownHostException` | Requested host is not configured                 |
| `WebhookException`     | Webhook verification or dispatch failure         |

Each exception carries a structured `context()` array (HTTP method, endpoint,
status, and a response body snippet) for logging and diagnostics.

```php
try {
    $messaging->sendText('5511999999999@c.us', 'Hello');
} catch (\DenLopes\Waha\Exceptions\RateLimitException $e) {
    // back off and retry later
} catch (\DenLopes\Waha\Exceptions\WahaException $e) {
    report($e);
}
```

## Architecture

```
src/
├── Concerns/              SendsRequests — shared HTTP plumbing for services
├── Contracts/             HttpClient, HostRegistry, ApiKeyProvider, SessionRouter, PinStore, Chat, Message, Conversation
├── Data/
│   ├── Input/             Request DTOs (serialized to WAHA payloads)
│   ├── Output/            Response/event DTOs (built from WAHA payloads)
│   ├── App.php            Built-in app definition (typed per-app config)
│   ├── HostConfig.php     Host definition value object
│   └── Data.php           Base DTO with fromArray()/fromJson()/toArray()/toJson()
├── Debug/                 DebugStore — last() / lastCurl() capture
├── Enums/                 Backed string enums for statuses, sort fields, events…
├── Exceptions/            Domain-specific exception hierarchy
├── Facades/               Waha — static facade for the SDK
├── Http/                  HttpClient — HTTP client (JSON + binary + retries)
├── Models/                Host, SessionPin
├── Pin/                   DbPinStore — session → host persistence
├── Registry/              ConfigHostRegistry, DbHostRegistry
├── Resources/             Chat, Message, Conversation (fluent handles)
├── Routing/               NullRouter, PinningRouter
├── Security/              ConfigApiKeyProvider
├── Services/              One class per WAHA API area
├── Support/               Pacing
├── Webhooks/              Verification, route, dispatch, handlers, events, models
├── Client.php             Container entry point (resource factory)
├── Session.php            Session name value object
└── WahaServiceProvider.php Config merge, migrations, bindings

config/
├── waha.php               Main configuration
└── logging.php            waha / wahaError channel defaults

database/migrations/       waha_hosts, waha_session_pins, waha_webhook_events
tests/                     PHPUnit suite
```

### Request layer

`DenLopes\Waha\Http\HttpClient` (bound to `DenLopes\Waha\Contracts\HttpClient`)
is the only place that talks HTTP. It:

- builds the Laravel HTTP client with the configured base URL and `X-Api-Key`;
- retries transient HTTP failures (`429`, `5xx`) and connection errors for
  **idempotent methods only**, with exponential backoff plus jitter — writes are
  never retried, to avoid duplicate messages;
- sends JSON requests and decodes the response;
- downloads binary responses (QR images, screenshots, media) and negotiates the
  binary representation via the `Accept` header;
- translates HTTP failures into typed exceptions.

`SendsRequests` is the trait consumed by every service. It injects the HTTP
client through the constructor and provides `send()` and `download()` helpers
that normalize failures into domain exceptions.

The HTTP client records its last request and response in `DebugStore`, which is
useful for troubleshooting:

```php
$debug = app(\DenLopes\Waha\Debug\DebugStore::class);
$debug->last();     // last masked request/response
$debug->lastCurl(); // last request as a copy-pasteable curl command
```

## Coverage

The service layer covers every area exposed by the WAHA OpenAPI document:

| Service              | Area                                  |
| -------------------- | ------------------------------------- |
| `SessionService`     | Session lifecycle and info            |
| `PairingService`     | QR, code, passkey pairing, screenshots |
| `ProfileService`     | Profile name/status/picture           |
| `MessagingService`   | Sending messages and reactions        |
| `ChatsService`       | Chats, messages, pinning, archiving   |
| `GroupsService`      | Group management and settings         |
| `ContactsService`    | Contacts and number checks            |
| `LidsService`        | LID ↔ phone number mappings           |
| `LabelsService`      | Labels (WhatsApp Business)            |
| `ChannelsService`    | Channels/newsletters                  |
| `StatusService`      | Status (stories)                      |
| `PresenceService`    | Presence management                   |
| `CallsService`       | Call rejection                        |
| `EventsService`      | Event (RSVP) messages                 |
| `MediaService`       | Media conversion                      |
| `ApiKeysService`     | API key management                    |
| `AppsService`        | Built-in apps and the MCP endpoint    |
| `ObservabilityService` | Ping, health, server, debugging     |

## Testing

The package uses Orchestra Testbench, so the suite runs standalone — no host
Laravel application required.

```bash
composer install
composer test        # vendor/bin/phpunit
composer pint        # vendor/bin/pint
composer pint:test   # vendor/bin/pint --test
```

`WahaTestCase` extends `Orchestra\Testbench\TestCase` and registers
`WahaServiceProvider` via `getPackageProviders()`, so Laravel-booted tests run
against an in-memory application.
