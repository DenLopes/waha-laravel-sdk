<?php

declare(strict_types=1);

namespace DenLopes\Waha\Support;

use DenLopes\Waha\Resources\Conversation;
use InvalidArgumentException;

/**
 * Pure spintax utility with no dependency on the conversation transport.
 *
 * Supports `{a|b|c}` alternation, including nesting. Callers resolve a message
 * before handing it to {@see Conversation::send()}.
 */
final class Spintax
{
    /**
     * Resolve the template into one random full expansion.
     *
     * @throws InvalidArgumentException When the template is malformed.
     */
    public static function parse(string $template): string
    {
        self::validate($template);

        return self::expand($template);
    }

    /**
     * The total number of distinct full expansions the template can produce.
     *
     * @throws InvalidArgumentException When the template is malformed.
     */
    public static function count(string $template): int
    {
        self::validate($template);

        return self::countExpansions($template);
    }

    /**
     * @throws InvalidArgumentException When braces are unbalanced or a branch is empty.
     */
    public static function validate(string $template): void
    {
        if (!self::balanced($template)) {
            throw new InvalidArgumentException('Spintax template has unbalanced braces.');
        }

        if (!self::branchesValid($template)) {
            throw new InvalidArgumentException('Spintax template contains an empty branch.');
        }
    }

    private static function expand(string $template): string
    {
        $open = strpos($template, '{');

        if ($open === false) {
            return $template;
        }

        $close = self::matchingBrace($template, $open);

        $inner = substr($template, $open + 1, $close - $open - 1);
        $branches = self::splitTopLevel($inner, '|');
        $branch = $branches[array_rand($branches)];

        return substr($template, 0, $open)
            .self::expand($branch.substr($template, $close + 1));
    }

    private static function countExpansions(string $template): int
    {
        $open = strpos($template, '{');

        if ($open === false) {
            return 1;
        }

        $close = self::matchingBrace($template, $open);

        $inner = substr($template, $open + 1, $close - $open - 1);
        $branches = self::splitTopLevel($inner, '|');
        $remainder = substr($template, $close + 1);

        $sum = 0;

        foreach ($branches as $branch) {
            $sum += self::countExpansions($branch);
        }

        return $sum * self::countExpansions($remainder);
    }

    private static function balanced(string $template): bool
    {
        $depth = 0;
        $length = strlen($template);

        for ($i = 0; $i < $length; $i++) {
            if ($template[$i] === '{') {
                $depth++;
            } elseif ($template[$i] === '}') {
                $depth--;

                if ($depth < 0) {
                    return false;
                }
            }
        }

        return $depth === 0;
    }

    private static function branchesValid(string $template): bool
    {
        $open = strpos($template, '{');

        if ($open === false) {
            return true;
        }

        $close = self::matchingBrace($template, $open);

        $inner = substr($template, $open + 1, $close - $open - 1);
        $branches = self::splitTopLevel($inner, '|');

        foreach ($branches as $branch) {
            if ($branch === '' || !self::branchesValid($branch)) {
                return false;
            }
        }

        return self::branchesValid(substr($template, $close + 1));
    }

    private static function matchingBrace(string $template, int $open): int
    {
        $depth = 0;
        $length = strlen($template);

        for ($i = $open; $i < $length; $i++) {
            if ($template[$i] === '{') {
                $depth++;
            } elseif ($template[$i] === '}') {
                $depth--;

                if ($depth === 0) {
                    return $i;
                }
            }
        }

        // Unreachable after validate(), but keeps the return type honest.
        return strlen($template);
    }

    /**
     * Split a block's contents on a delimiter, ignoring delimiters nested inside braces.
     *
     * @return list<string>
     */
    private static function splitTopLevel(string $value, string $delimiter): array
    {
        $parts = [];
        $depth = 0;
        $current = '';
        $length = strlen($value);

        for ($i = 0; $i < $length; $i++) {
            $char = $value[$i];

            if ($char === '{') {
                $depth++;
            } elseif ($char === '}') {
                $depth--;
            }

            if ($char === $delimiter && $depth === 0) {
                $parts[] = $current;
                $current = '';
            } else {
                $current .= $char;
            }
        }

        $parts[] = $current;

        return $parts;
    }
}
