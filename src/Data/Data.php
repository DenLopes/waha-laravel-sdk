<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data;

use BackedEnum;
use DenLopes\Waha\Exceptions\IntegrationException;
use ReflectionClass;
use ReflectionProperty;
use UnitEnum;

/**
 * Base class for WAHA data transfer objects.
 *
 * Response DTOs implement {@see self::fromArray()} to map raw API payloads into
 * typed value objects. Request DTOs additionally inherit {@see self::toArray()}
 * (and {@see self::toJson()}) to serialize themselves back into the payload WAHA
 * expects. The serializer walks public constructor-promoted properties, skips
 * null values (WAHA treats an omitted key as "leave unchanged" for most optional
 * fields), and recursively serializes nested DTOs, backed enums and arrays.
 */
abstract readonly class Data
{
    /**
     * Build a DTO from a raw WAHA API response array.
     *
     * @param  array<string, mixed>  $data  Raw API payload.
     */
    abstract public static function fromArray(array $data): static;

    /**
     * Build a DTO from a raw WAHA API JSON string.
     */
    public static function fromJson(string $json): static
    {
        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new IntegrationException(
                'Failed to decode WAHA payload: '.$e->getMessage(),
                0,
                $e,
            );
        }

        return static::fromArray((array) $data);
    }

    /**
     * Extract a nullable string from a raw payload without throwing on an
     * unexpected (array/object) shape.
     *
     * @param  array<string, mixed>  $data
     */
    protected static function string(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;

        return is_scalar($value) ? (string) $value : null;
    }

    /**
     * Extract a nullable array from a raw payload without throwing on an
     * unexpected (scalar/object) shape.
     *
     * @param  array<string, mixed>  $data
     */
    protected static function arrayValue(array $data, string $key): ?array
    {
        $value = $data[$key] ?? null;

        return is_array($value) ? $value : null;
    }

    /**
     * Extract a nullable integer from a raw payload without throwing on an
     * unexpected shape.
     *
     * @param  array<string, mixed>  $data
     */
    protected static function intValue(array $data, string $key): ?int
    {
        $value = $data[$key] ?? null;

        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * Extract a nullable boolean from a raw payload without throwing on an
     * unexpected shape.
     *
     * @param  array<string, mixed>  $data
     */
    protected static function boolValue(array $data, string $key): ?bool
    {
        if (!array_key_exists($key, $data) || $data[$key] === null) {
            return null;
        }

        return (bool) $data[$key];
    }

    /**
     * Serialize the DTO into the associative array shape WAHA accepts.
     *
     * @return array<string, mixed>
     */
    public function toArray(bool $includeNull = false): array
    {
        return $this->serialize($this, $includeNull);
    }

    /**
     * Serialize the DTO to JSON.
     */
    public function toJson(int $flags = 0): string
    {
        try {
            return json_encode($this->toArray(), $flags | JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new IntegrationException(
                'Failed to encode WAHA payload: '.$e->getMessage(),
                0,
                $e,
            );
        }
    }

    /**
     * Recursively serialize a value into a JSON-friendly representation.
     */
    private function serialize(mixed $value, bool $includeNull = false): mixed
    {
        if ($value instanceof self) {
            $result = [];
            $reflection = new ReflectionClass($value);

            foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
                if (!$property->isInitialized($value)) {
                    continue;
                }

                $item = $property->getValue($value);

                if ($item === null && !$includeNull) {
                    continue;
                }

                $result[$property->getName()] = $this->serialize($item, $includeNull);
            }

            return $result;
        }

        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if ($value instanceof UnitEnum) {
            return $value->name;
        }

        if ($value instanceof \Stringable) {
            return (string) $value;
        }

        if (is_array($value)) {
            return array_map(fn (mixed $item): mixed => $this->serialize($item, $includeNull), $value);
        }

        return $value;
    }
}
