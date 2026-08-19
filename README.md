# WAHA Integration

This package is a typed, documented client for the [WAHA](https://waha.devlike.pro/)
(WhatsApp HTTP API) server. It wraps the HTTP endpoints in small, injectable
service classes and maps JSON payloads to/from PHP data-transfer objects (DTOs)
so the rest of the codebase never has to touch raw request/response arrays.

## Table of contents

- [Using in another project](#using-in-another-project)
- [Configuration](#configuration)
- [Architecture](#architecture)
- [Quick start](#quick-start)
- [Sessions](#sessions)
- [DTOs](#dtos)
- [Errors](#errors)
- [Webhooks](#webhooks)
- [Coverage](#coverage)

## Installation

```bash
composer require denlopes/waha-laravel-sdk
```

Laravel package discovery registers `DenLopes\Waha\WahaServiceProvider`
automatically. Publish the config to customize it:

```bash
php artisan vendor:publish --tag="waha-config"
```

Add the WAHA connection settings to your `.env` (see
[Configuration](#configuration)).

### Local development

When developing the package alongside an application, register it as a
Composer path repository in the application's `composer.json`:

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

## Configuration

All settings are read through the standard Laravel `config()` helper and come
from `config/waha.php` / the environment.

| Config key          | Env var                | Default                  | Description                                   |
| ------------------- | ---------------------- | ------------------------ | --------------------------------------------- |
| `waha.base_url`     | `WAHA_BASE_URL`        | `http://localhost:3000`  | Base URL of the WAHA server.                  |
| `waha.api_key`      | `WAHA_API_KEY`         | _(none)_                 | Secret sent via the `X-Api-Key` header.       |
| `waha.default_session` | `WAHA_DEFAULT_SESSION` | `default`                | Session used when none is provided explicitly. |
| `waha.timeout`      | `WAHA_TIMEOUT`         | `30`                     | HTTP request timeout, in seconds.             |
| `waha.connect_timeout` | `WAHA_CONNECT_TIMEOUT` | `5`                      | TCP connection timeout, in seconds.          |
| `waha.retry_attempts`  | `WAHA_RETRY_ATTEMPTS`  | `3`                      | Retries for transient failures (connection, 429, 5xx). |
| `waha.retry_delay_ms`  | `WAHA_RETRY_DELAY_MS`  | `200`                    | Initial retry backoff in ms (exponential).    |

Example `.env`:

```dotenv
WAHA_BASE_URL=http://localhost:3000
WAHA_API_KEY=your-secret-key
WAHA_DEFAULT_SESSION=default
WAHA_TIMEOUT=30
WAHA_CONNECT_TIMEOUT=5
WAHA_RETRY_ATTEMPTS=3
WAHA_RETRY_DELAY_MS=200
```

## Multi-host

Configure `waha.hosts` to talk to more than one WAHA server. When empty, the
legacy single-host keys above are used as the `primary` host.

```php
// config/waha.php
'default_host' => env('WAHA_DEFAULT_HOST', 'primary'),

'hosts' => [
    'primary' => [
        'base_url'        => env('WAHA_PRIMARY_URL'),
        'api_key'         => env('WAHA_PRIMARY_API_KEY'),
        'api_key_header'  => env('WAHA_API_KEY_HEADER', 'X-Api-Key'),
        'default_session' => env('WAHA_PRIMARY_DEFAULT_SESSION', 'default'),
        'mode'            => env('WAHA_PRIMARY_MODE', 'admin_fallback'), // admin_fallback|strict_session_key
        'session_keys'    => [],
    ],
    'secondary' => [
        'base_url' => env('WAHA_SECONDARY_URL'),
        'api_key'  => env('WAHA_SECONDARY_API_KEY'),
    ],
],
```

Host selection is abstracted behind `HostRegistry`, `ApiKeyProvider`, and
`SessionRouter` contracts.

### DB-backed hosts

Set `WAHA_REGISTRY_DRIVER=db` to read hosts from the `waha_hosts` table instead
of `config/waha.php`. Publish/run the migrations (`php artisan migrate`), then
seed the table with your hosts. Each host is keyed by a unique `key` and can
optionally define per-session API keys.

### Session → host pinning

Set `WAHA_ROUTING_DRIVER=pin` to resolve the host from the `waha_session_pins`
table (session name → host key), falling back to `default_host` when unknown.
This is what lets each company/tenant use its own WhatsApp number — and, when it
grows, its own WAHA host — without hardcoding that mapping in the SDK.

## Logging

The package merges two dedicated channels into the host application's logging
config: `waha` (request/response lifecycle) and `wahaError` (failures). Override
them in your own `config/logging.php` if you want different drivers, paths, or
levels.

## Architecture

```
src/
├── Concerns/              SendsWahaRequests — shared HTTP plumbing for services
├── Contracts/             Resource contracts (type-hinting/mocking)
│   └── WahaClientInterface.php HTTP transport contract (fake-able seam)
├── Data/
│   ├── Input/             Request DTOs (serialized to WAHA payloads)
│   ├── Output/            Response/event DTOs (built from WAHA payloads)
│   ├── AppData.php        Shared "app" DTO
│   ├── SessionActionsData.php
│   ├── Settings*.php      Group security settings DTOs
│   └── WahaData.php       Base DTO with fromArray()/toArray()
├── Enums/                 Backed string enums for statuses, sort fields, events…
├── Exception/             Domain-specific exceptions
├── Http/
│   └── WahaRequest.php    HTTP client (JSON + binary download + retries)
├── Fluent/
│   ├── WahaChat.php       Fluent wrapper around a chat
│   ├── WahaMessage.php    Fluent wrapper around a message
│   └── WahaManager.php    Container-resolvable entry point
├── Services/              One class per WAHA API area
├── Webhooks/             Verification, route, dispatch, handlers and events
├── Support/
│   └── WahaSession.php    Session name value object
├── tests/                 PHPUnit suite for DTOs, services and HTTP client
└── WahaServiceProvider.php Config merge, config publishing and client binding
```

### Request layer

`WahaRequest` (bound to `WahaClientInterface` by `WahaServiceProvider`) is the
only place that talks HTTP. It:

- builds the Laravel HTTP client with the configured base URL and `X-Api-Key`;
- retries transient failures (connection errors, `429`, `5xx`) with exponential
  backoff;
- sends JSON requests and decodes the response;
- downloads binary responses (QR images, screenshots, CPU profiles, media);
- translates HTTP failures into typed exceptions.

`SendsWahaRequests` is the trait consumed by every service. It injects
`WahaClientInterface` through the constructor (so services are container-resolvable
and can be unit-tested with a fake client) and provides `send()` and `download()`
helpers that normalize failures into domain exceptions.

### Services

Services map 1:1 to WAHA API areas and follow a consistent naming convention:
`list*`, `get*`, `create*`, `update*`, `delete*`, `send*`, `set*`.

```php
use DenLopes\Waha\Services\ChattingService;

$chatting = app(ChattingService::class);

$message = $chatting->sendText('5511999999999@c.us', 'Hello from Laravel');
```

## Quick start

```php
use DenLopes\Waha\Services\ChattingService;
use DenLopes\Waha\Services\SessionService;
use DenLopes\Waha\Data\Input\SessionCreateRequestData;
use DenLopes\Waha\Data\Input\RemoteFileData;

$sessions = app(SessionService::class);
$chatting = app(ChattingService::class);

// Create (and start) a session.
$session = $sessions->createSession(new SessionCreateRequestData(name: 'default'));

// Send text.
$chatting->sendText(
    chatId: '5511999999999@c.us',
    text: 'Hello!',
);

// Send an image by URL.
$chatting->sendImage(
    chatId: '5511999999999@c.us',
    file: new RemoteFileData(mimetype: 'image/jpeg', url: 'https://example.com/pic.jpg'),
);
```

## Fluent

On top of the services there are two small, fluent resource classes for the most
common chat/message flows. They keep the session and ID state and lazily resolve
the services they need, so they can be constructed with `new`.

```php
use DenLopes\Waha\Data\Input\RemoteFileData;
use DenLopes\Waha\Fluent\WahaChat;
use DenLopes\Waha\Support\WahaSession;

$chat = new WahaChat(WahaSession::default(), '5511999999999@c.us');

$message = $chat->sendMessage('Hello from Laravel');

// Every send* returns a message handle, so message actions chain directly.
$chat->sendImage(new RemoteFileData('image/jpeg', 'https://example.com/pic.jpg'))
    ->react('🔥');

$existing = $chat->message($message->id()); // lazy handle, no I/O

$existing
    ->read()
    ->pin()
    ->update('Updated text')
    ->delete();
```

`WahaChat` exposes fluent chat-level actions: `sendMessage`, `sendImage`,
`sendFile`, `sendVoice`, `sendVideo`, `sendPoll`, `sendLocation`,
`sendContactVcard`, `sendList`, `sendLinkCustomPreview`, and `forwardMessage`
(all of which return a `WahaMessage`), plus `startTyping`, `stopTyping`,
`setReaction`, `setStar`, `sendSeen`, `pinMessage`, `unpinMessage`, `archive`,
`unarchive`, `markUnread`, `clearMessages`, and `delete` (which return `$this`
for chaining), and the `message`, `getMessage`, and `getMessages` lookups.

`WahaMessage` exposes message-level actions: `get`, `refresh`, `read`, `react`,
`star`, `pin`, `unpin`, `update`, `forward`, and `delete`. A message can also be
looked up directly with the static finder:

```php
use DenLopes\Waha\Fluent\WahaMessage;

$message = WahaMessage::find(
    chatId: '5511999999999@c.us',
    id: 'false_5511999999999@c.us_XXXXXXXX',
);
```

Both accept optional service constructor arguments so tests can inject mocks.

### Manager

`WahaManager` is a container-resolvable entry point that returns resource
handles without performing any network I/O:

```php
use DenLopes\Waha\Fluent\WahaManager;
use DenLopes\Waha\Support\WahaSession;

$waha = app(WahaManager::class);

$chat = $waha->chat('5511999999999@c.us');
$chat = $waha->chat('5511999999999@c.us', 'sales'); // name or value object
$chat = $waha->chat('5511999999999@c.us', WahaSession::from('sales'));

$message = $waha->message('5511999999999@c.us', 'false_...@c.us_...');
$session = $waha->session(); // configured default
$session = $waha->session('sales'); // named session
```

### Contracts

`WahaChatContract` and `WahaMessageContract` in `app/Waha/Contracts` define the
resource API for type-hinting and mocking. The concrete `WahaChat` and
`WahaMessage` (in `app/Waha/Fluent`) implement them.

## Sessions

A session name is wrapped in the `WahaSession` value object to give it nominal
typing (a session can no longer be confused with a chat ID or message ID).

```php
use DenLopes\Waha\Support\WahaSession;

$session = WahaSession::from('default');
$session = WahaSession::default(); // uses waha.default_session

$session->value();   // string
(string) $session;   // string
```

Most service methods accept `?WahaSession $session = null` and fall back to the
configured default session when omitted.

## DTOs

- **Request DTOs** live in `app/Waha/Data/Input` and extend `WahaData`. They are
  constructed with named arguments and serialized with `toArray()`/`toJson()`.
- **Response/event DTOs** live in `app/Waha/Data/Output` and are built from API
  arrays with `fromArray()`.

The `WahaData` serializer walks public constructor-promoted properties, skips
`null` values (WAHA treats an omitted key as "leave unchanged"), and recursively
serializes nested DTOs, backed enums and arrays.

```php
use DenLopes\Waha\Data\Input\ApiKeyRequestData;
use DenLopes\Waha\Data\SessionActionsData;

$request = new ApiKeyRequestData(
    isAdmin: false,
    session: 'default',
    isActive: true,
    actions: new SessionActionsData(
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

## Errors

Every failure is thrown as a subclass of `DenLopes\Waha\Exception\WahaException`, so
callers can catch the base type for "any WAHA problem" or a specific subtype for
targeted handling.

| Exception                   | HTTP trigger                                   |
| --------------------------- | ---------------------------------------------- |
| `WahaCredentialsException`  | Missing API key, or `401`/`403`                |
| `NoDataException`           | `404`                                          |
| `WahaRateLimitException`    | `429`                                          |
| `WahaRequestException`      | `400`/`422`                                    |
| `WahaServerException`       | `5xx`                                          |
| `WahaConnectionException`   | Connection failure / timeout                   |
| `WahaIntegrationException`  | JSON decode failures and unclassified failures |

Each exception carries a structured `context()` array (HTTP method, endpoint,
status and response body snippet) for logging and diagnostics.

```php
try {
    $chatting->sendText('5511999999999@c.us', 'Hello');
} catch (\DenLopes\Waha\Exception\WahaRateLimitException $e) {
    // back off and retry later
} catch (\DenLopes\Waha\Exception\WahaException $e) {
    report($e);
}
```

## Webhooks

When enabled (the default), the service provider registers a stateless route for
inbound WAHA deliveries. It verifies the request, parses it into a typed
`WebhookData`, then dispatches it.

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

- **Laravel event** — `DenLopes\Waha\Webhooks\Events\WahaWebhookReceived` is always
  fired and carries the parsed `WebhookData` plus the raw body and request ID.
- **Configured handlers** — map WAHA event names to handler classes:

```php
// config/waha.php
'webhooks' => [
    'handlers' => [
        'message.any' => \DenLopes\Waha\Handlers\MessageHandler::class,
        'message.*'   => \DenLopes\Waha\Handlers\AnyMessageHandler::class,
    ],
],
```

Handlers implement `DenLopes\Waha\Webhooks\Contracts\WahaWebhookHandler`.

### Processing mode

- `sync` (default) — runs handlers inline during the HTTP request.
- `queue` — dispatches `ProcessWahaWebhookJob` and returns immediately
  (`WAHA_WEBHOOKS_PROCESSING_MODE=queue`).

### Parsing

`WebhookData::fromArray()` maps `payload` to the most specific DTO for the event
(e.g. `WAMessageData` for `message`). Unrecognized events keep their raw array.

### Storage

Set `WAHA_WEBHOOKS_STORE_ENABLED=true` to persist verified deliveries to the
`waha_webhook_events` table (id, event, session, request_id, host_key, payload).
Run the package migrations with `php artisan migrate`; the migration is also
publishable via `php artisan vendor:publish --tag="waha-migrations"`.

## Coverage

The service layer covers every area exposed by the WAHA OpenAPI document:

| Service              | Area                                  |
| -------------------- | ------------------------------------- |
| `SessionService`     | Session lifecycle and info            |
| `PairingService`     | QR, code, passkey pairing, screenshots |
| `ProfileService`     | Profile name/status/picture           |
| `ChattingService`    | Sending messages and reactions        |
| `ChatsService`       | Chats, messages, pinning, archiving   |
| `GroupsService`      | Group management and settings         |
| `ContactsService`    | Contacts and number checks            |
| `LidsService`        | LID <-> phone number mappings         |
| `LabelsService`      | Labels (WhatsApp Business)            |
| `ChannelsService`    | Channels/newsletters                  |
| `StatusService`      | Status (stories)                      |
| `PresenceService`    | Presence management                   |
| `CallsService`       | Call rejection                        |
| `EventsService`      | Event (RSVP) messages                 |
| `MediaService`       | Media conversion                      |
| `ApiKeysService`     | API key management                    |
| `AppsService`        | Built-in apps and the MCP endpoint    |
| `ObservabilityService` | Ping, health, server, debugging      |
