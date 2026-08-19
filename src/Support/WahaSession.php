<?php

declare(strict_types=1);

namespace DenLopes\Waha\Support;

use InvalidArgumentException;

/**
 * A WhatsApp session name value object.
 *
 * Wrapping the raw string in a dedicated type gives nominal typing: a session
 * name can no longer be silently confused with a chat ID, message ID or phone
 * number. It also centralizes the empty-name invariant and the configured
 * default session lookup.
 */
final readonly class WahaSession
{
    private string $value;

    /**
     * @throws InvalidArgumentException When the session name is empty.
     */
    public function __construct(string $value)
    {
        $value = trim($value);

        if ($value === '') {
            throw new InvalidArgumentException('WAHA session name cannot be empty.');
        }

        $this->value = $value;
    }

    /**
     * Create a session from an explicit name.
     */
    public static function from(string $value): self
    {
        return new self($value);
    }

    /**
     * Create a session from the configured default session name.
     */
    public static function default(): self
    {
        return new self((string) config('waha.default_session', 'default'));
    }

    /**
     * The normalized session name.
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * Compare this session against another session.
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
