<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Infrastructure\Doctrine\Lexer;

use Doctrine\Common\Lexer\AbstractLexer;

/**
 * @extends AbstractLexer<TokenType, string>
 */
class Lexer extends AbstractLexer
{
    protected function getCatchablePatterns(): array
    {
        return [
            '[a-zA-Z_\\\][a-z0-9_]*(?:\\\[a-zA-Z_][a-zA-Z0-9_]*)*',
            '(?:[0-9]+(?:[\.][0-9]+)*)(?:e[+-]?[0-9]+)?', // numbers
            '\'(?:[^\']|\'\')*\'', // quoted strings
            '\?[0-9]*|:[a-z_][a-z0-9_]*', // parameters
        ];
    }

    protected function getNonCatchablePatterns(): array
    {
        return ['\s+', '--.*', '(.)'];
    }

    protected function getType(string &$value): TokenType
    {
        $type = TokenType::T_NONE;

        switch (true) {
            case is_numeric($value):
                if (str_contains($value, '.') || stripos($value, 'e') !== false) {
                    return TokenType::T_FLOAT;
                }

                return TokenType::T_INTEGER;

            case $value[0] === '\'':
                $value = str_replace('\'\'', '\'', substr($value, 1, \strlen($value) - 2));

                return TokenType::T_STRING;

            case ctype_alpha($value[0]):
                $name = \sprintf('%s::T_%s', TokenType::class, mb_strtoupper($value));

                if (\defined($name)) {
                    $type = \constant($name);

                    if ($type->value > 100) {
                        return $type;
                    }
                }

                return TokenType::T_IDENTIFIER;

            case $value[0] === ':':
                $value = mb_substr($value, 1);
                $name = \sprintf('%s::T_%s', TokenType::class, mb_strtoupper($value));

                if (\defined($name)) {
                    $type = \constant($name);

                    if ($type->value > 100) {
                        return $type;
                    }
                }

                // Recognize symbols
                // no break
            case $value === '.':
                return TokenType::T_DOT;
            case $value === ',':
                return TokenType::T_COMMA;
            case $value === '(':
                return TokenType::T_OPEN_PARENTHESIS;
            case $value === ')':
                return TokenType::T_CLOSE_PARENTHESIS;
            case $value === '=':
                return TokenType::T_EQUALS;
            case $value === '>':
                return TokenType::T_GREATER_THAN;
            case $value === '<':
                return TokenType::T_LOWER_THAN;
            case $value === '!':
                return TokenType::T_NEGATE;

                // Default
            default:
                // Do nothing
        }

        return $type;
    }
}
