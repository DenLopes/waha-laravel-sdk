<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;
use DenLopes\Waha\Enums\LinkPreviewMode;

/**
 * Configuration for the built-in ChatWoot integration app.
 */
final readonly class ChatWootAppConfig extends Data
{
    public function __construct(
        public string $url,
        public ?int $accountId,
        public string $accountToken,
        public ?int $inboxId,
        public string $inboxIdentifier,
        public LinkPreviewMode $linkPreview = LinkPreviewMode::OFF,
        public string $locale = 'en-US',
        public ?array $templates = null,
        public ?ChatWootCommandsConfig $commands = null,
        public ?ChatWootConversationsConfig $conversations = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            url: (string) ($data['url'] ?? ''),
            accountId: self::intValue($data, 'accountId'),
            accountToken: (string) ($data['accountToken'] ?? ''),
            inboxId: self::intValue($data, 'inboxId'),
            inboxIdentifier: (string) ($data['inboxIdentifier'] ?? ''),
            linkPreview: LinkPreviewMode::tryFrom((string) ($data['linkPreview'] ?? '')) ?? LinkPreviewMode::OFF,
            locale: (string) ($data['locale'] ?? 'en-US'),
            templates: self::arrayValue($data, 'templates'),
            commands: isset($data['commands']) && is_array($data['commands'])
                ? ChatWootCommandsConfig::fromArray($data['commands'])
                : null,
            conversations: isset($data['conversations']) && is_array($data['conversations'])
                ? ChatWootConversationsConfig::fromArray($data['conversations'])
                : null,
        );
    }
}
