<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Lexer;

enum TokenType: int
{
    // All tokens that are not valid identifiers must be < 100
    case T_NONE = 1;
    case T_INTEGER = 2;
    case T_STRING = 3;
    case T_FLOAT = 4;
    case T_CLOSE_PARENTHESIS = 5;
    case T_OPEN_PARENTHESIS = 6;
    case T_COMMA = 7;
    case T_DOT = 8;
    case T_EQUALS = 9;
    case T_GREATER_THAN = 10;
    case T_LOWER_THAN = 11;
    case T_NEGATE = 12;
    case T_OPEN_CURLY_BRACE = 13;
    case T_CLOSE_CURLY_BRACE = 14;

    // All tokens that are identifiers or keywords that could be considered as identifiers should be >= 100
    case T_IDENTIFIER = 100;

    // All keyword tokens should be >= 200
    case T_AND = 200;
    case T_OR = 201;
    case T_EMPTY = 202;
    case T_IN = 203;
    case T_TRUE = 204;
    case T_FALSE = 205;
    case T_NOT = 206;
    case T_NULL = 207;
    case T_LIKE = 208;
    case T_FIRST_DAY_OF_THE_MONTH = 209;
    case T_FIRST_DAY_OF_LAST_MONTH = 210;
    case T_LAST_DAY_OF_THE_MONTH = 211;
    case T_LAST_DAY_OF_LAST_MONTH = 212;

    /**
     * @return array<string, TokenType>
     */
    public static function scalar(): array
    {
        return [
            'INTEGER' => self::T_INTEGER,
            'STRING' => self::T_STRING,
            'FLOAT' => self::T_FLOAT,
        ];
    }

    /**
     * @return array<string, TokenType>
     */
    public static function filters(): array
    {
        return [
            'FIRST_DAY_OF_THE_MONTH' => self::T_FIRST_DAY_OF_THE_MONTH,
            'FIRST_DAY_OF_LAST_MONTH' => self::T_FIRST_DAY_OF_LAST_MONTH,
            'LAST_DAY_OF_THE_MONTH' => self::T_LAST_DAY_OF_THE_MONTH,
            'LAST_DAY_OF_LAST_MONTH' => self::T_LAST_DAY_OF_LAST_MONTH,
        ];
    }

    public static function tryFromFilter(string $filter): self
    {
        return match (mb_strtoupper($filter)) {
            'FIRST_DAY_OF_THE_MONTH' => self::T_FIRST_DAY_OF_THE_MONTH,
            'FIRST_DAY_OF_LAST_MONTH' => self::T_FIRST_DAY_OF_LAST_MONTH,
            'LAST_DAY_OF_THE_MONTH' => self::T_LAST_DAY_OF_THE_MONTH,
            'LAST_DAY_OF_LAST_MONTH' => self::T_LAST_DAY_OF_LAST_MONTH,
            default => self::T_NONE,
        };
    }
}
