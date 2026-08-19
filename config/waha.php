<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WAHA Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL where the WAHA (WhatsApp HTTP API) server is running.
    | Defaults to the standard local Docker setup.
    |
    */
    'base_url' => env('WAHA_BASE_URL', 'http://localhost:3000'),

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | The secret API key used to authenticate against the WAHA server.
    | It is sent via the "X-Api-Key" header.
    |
    */
    'api_key' => env('WAHA_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Default Session
    |--------------------------------------------------------------------------
    |
    | The session name used when an explicit session is not provided.
    |
    */
    'default_session' => env('WAHA_DEFAULT_SESSION', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Default Host
    |--------------------------------------------------------------------------
    |
    | The host key used when no explicit host is provided.
    |
    */
    'default_host' => env('WAHA_DEFAULT_HOST', 'primary'),

    /*
    |--------------------------------------------------------------------------
    | Hosts
    |--------------------------------------------------------------------------
    |
    | Multi-host configuration. When empty, the legacy single-host keys above
    | are used as the `primary` host. Each host may define:
    |
    |   - base_url
    |   - api_key / api_key_header
    |   - default_session
    |   - mode (admin_fallback|strict_session_key)
    |   - session_keys (session => api_key map)
    |   - webhook_secret (when using per-host webhooks)
    |
    */
    'hosts' => [
        // 'primary' => [
        //     'base_url'        => env('WAHA_PRIMARY_URL'),
        //     'api_key'         => env('WAHA_PRIMARY_API_KEY'),
        //     'api_key_header'  => env('WAHA_API_KEY_HEADER', 'X-Api-Key'),
        //     'default_session' => env('WAHA_PRIMARY_DEFAULT_SESSION', 'default'),
        //     'mode'            => env('WAHA_PRIMARY_MODE', 'admin_fallback'),
        //     'session_keys'    => [],
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Registry Driver
    |--------------------------------------------------------------------------
    |
    | Where host definitions come from:
    |   - config: `waha.hosts` above (with legacy fallback)
    |   - db:     the `waha_hosts` table
    |
    */
    'registry' => [
        'driver' => env('WAHA_REGISTRY_DRIVER', 'config'), // config|db
    ],

    /*
    |--------------------------------------------------------------------------
    | Routing Driver
    |--------------------------------------------------------------------------
    |
    | How a session is resolved to a host:
    |   - none: explicit host or `default_host`
    |   - pin:  session → host mapping stored in `waha_session_pins`
    |
    */
    'routing' => [
        'driver' => env('WAHA_ROUTING_DRIVER', 'none'), // none|pin
    ],

    /*
    |--------------------------------------------------------------------------
    | Request Timeout (seconds)
    |--------------------------------------------------------------------------
    */
    'timeout' => (int) env('WAHA_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Connection Timeout (seconds)
    |--------------------------------------------------------------------------
    |
    | How long to wait for a TCP connection before failing. Kept separate from
    | the request timeout so a hung connect does not consume the full budget.
    |
    */
    'connect_timeout' => (int) env('WAHA_CONNECT_TIMEOUT', 5),

    /*
    |--------------------------------------------------------------------------
    | Retry Policy
    |--------------------------------------------------------------------------
    |
    | Transient failures (connection errors, 429 and 5xx) are retried up to
    | "retry_attempts" times with an exponential backoff starting at
    | "retry_delay_ms" milliseconds. Set attempts to 0 to disable retries.
    |
    */
    'retry_attempts' => (int) env('WAHA_RETRY_ATTEMPTS', 3),
    'retry_delay_ms' => (int) env('WAHA_RETRY_DELAY_MS', 200),

    /*
    |--------------------------------------------------------------------------
    | Conversations (anti-ban)
    |--------------------------------------------------------------------------
    |
    | Settings used by the fluent `Conversation` resource to send messages
    | in a human-like way and avoid being flagged as spam:
    |
    |   - humanize: markRead → startTyping → (typing delay) → stopTyping → send
    |   - typing_*: random typing duration, plus an amount per character
    |   - cooldown_*: random wait between consecutive messages
    |   - max_messages_per_window / window_seconds: pause once the cap is hit
    |
    | The defaults mirror WAHA's recommendation of sending only a handful of
    | messages per contact and waiting 30-60 seconds between messages.
    |
    */
    'conversations' => [
        'humanize'                    => (bool) env('WAHA_CONVERSATIONS_HUMANIZE', true),
        'typing_min_ms'               => (int) env('WAHA_CONVERSATIONS_TYPING_MIN_MS', 800),
        'typing_max_ms'               => (int) env('WAHA_CONVERSATIONS_TYPING_MAX_MS', 3000),
        'typing_per_character_ms'     => (float) env('WAHA_CONVERSATIONS_TYPING_PER_CHAR_MS', 60.0),
        'typing_pause_chance_percent' => (int) env('WAHA_CONVERSATIONS_TYPING_PAUSE_CHANCE_PERCENT', 12),
        'typing_pause_min_ms'         => (int) env('WAHA_CONVERSATIONS_TYPING_PAUSE_MIN_MS', 400),
        'typing_pause_max_ms'         => (int) env('WAHA_CONVERSATIONS_TYPING_PAUSE_MAX_MS', 1500),
        'cooldown_min_ms'             => (int) env('WAHA_CONVERSATIONS_COOLDOWN_MIN_MS', 30000),
        'cooldown_max_ms'             => (int) env('WAHA_CONVERSATIONS_COOLDOWN_MAX_MS', 60000),
        'max_messages_per_window'     => (int) env('WAHA_CONVERSATIONS_MAX_MESSAGES_PER_WINDOW', 4),
        'window_seconds'              => (int) env('WAHA_CONVERSATIONS_WINDOW_SECONDS', 3600),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhooks
    |--------------------------------------------------------------------------
    |
    | Inbound WAHA event delivery. The route is registered by the service
    | provider when `enabled` is true. HMAC verification uses the raw request
    | body and the configured secret (WAHA signs with sha512 by default).
    |
    */
    'webhooks' => [
        'enabled' => (bool) env('WAHA_WEBHOOKS_ENABLED', true),

        // Shared secret configured in WAHA. Required when `require_hmac` is true.
        'secret' => env('WAHA_WEBHOOK_SECRET'),

        'require_hmac' => (bool) env('WAHA_WEBHOOKS_REQUIRE_HMAC', true),

        // Allowed clock drift for the X-Webhook-Timestamp header, in ms.
        'max_clock_skew_ms' => (int) env('WAHA_WEBHOOKS_MAX_CLOCK_SKEW_MS', 300000),

        // Replay protection keyed by X-Webhook-Request-Id.
        'replay' => [
            'enabled'      => (bool) env('WAHA_WEBHOOKS_REPLAY_ENABLED', true),
            'ttl_seconds'  => (int) env('WAHA_WEBHOOKS_REPLAY_TTL_SECONDS', 900),
            'cache_prefix' => env('WAHA_WEBHOOKS_REPLAY_CACHE_PREFIX', 'waha:webhook:'),
        ],

        'route' => [
            'prefix'     => env('WAHA_WEBHOOKS_ROUTE_PREFIX', '/webhooks/waha'),
            'middleware' => ['api'],
        ],

        'processing' => [
            // 'sync' runs handlers in the HTTP request; 'queue' defers to a job.
            'mode'             => env('WAHA_WEBHOOKS_PROCESSING_MODE', 'sync'),
            'queue_connection' => env('WAHA_WEBHOOKS_QUEUE_CONNECTION'),
            'queue_name'       => env('WAHA_WEBHOOKS_QUEUE_NAME', 'default'),
        ],

        // Persist verified deliveries to the waha_webhook_events table.
        'store' => [
            'enabled' => (bool) env('WAHA_WEBHOOKS_STORE_ENABLED', false),
        ],

        // Map WAHA event names to handler classes (implement WebhookHandler).
        // A '*' suffix (e.g. "message.*") matches any event under that prefix.
        'handlers' => [
            // 'message.any' => \DenLopes\Waha\Handlers\MessageHandler::class,
        ],
    ],
];
