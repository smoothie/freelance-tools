<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Lexer;

use App\Application\Clock;
use App\Domain\Model\Expression;
use App\Domain\Model\FilterCriteria;
use App\Domain\Model\FilterExpressions;
use App\Domain\Service\FilterQueryParser;
use Doctrine\Common\Lexer\Token;

class FilterCriteriaQueryParser implements FilterQueryParser
{
    public function __construct(private Lexer $lexer, private Clock $clock)
    {
    }

    public function parse(FilterCriteria $query): FilterExpressions
    {
        $this->lexer->setInput($query->getQuery());
        $this->lexer->moveNext();

        $expressions = $this->extractExpressions($this->lexer->lookahead->type);

        return new FilterExpressions($expressions);
    }

    private function extractComparison(): Expression
    {
        $this->match(TokenType::T_OPEN_PARENTHESIS);
        $identifier = $this->extractIdentifier();
        $operator = $this->extractOperator();
        $value = $this->extractValue();
        $this->match(TokenType::T_CLOSE_PARENTHESIS);

        return new Expression($identifier, $operator, $value);
    }

    private function extractValue(): string|int|array|null
    {
        if ($this->lexer->isNextTokenAny(TokenType::filters())) {
            $this->match($this->lexer->lookahead->type);

            return match ($this->lexer->token->type) {
                TokenType::T_FIRST_DAY_OF_LAST_MONTH => $this->clock->firstDayOfLastMonth()->asDateString(),
                TokenType::T_LAST_DAY_OF_LAST_MONTH => $this->clock->lastDayOfLastMonth()->asDateString(),
                TokenType::T_FIRST_DAY_OF_THE_MONTH => $this->clock->firstDayOfTheMonth()->asDateString(),
                TokenType::T_LAST_DAY_OF_THE_MONTH => $this->clock->lastDayOfTheMonth()->asDateString(),
                default => throw new \LogicException('Unsupported filter "'.$this->lexer->lookahead->type.'"'),
            };
        }

        if ($this->lexer->isNextTokenAny(TokenType::scalar())) {
            $this->match($this->lexer->lookahead->type);

            return $this->lexer->token->value;
        }

        if (! $this->lexer->isNextToken(TokenType::T_OPEN_PARENTHESIS)) {
            $this->syntaxError(TokenType::T_OPEN_PARENTHESIS->name);
        }

        $this->match($this->lexer->lookahead->type);

        $value = [];
        while ($this->lexer->isNextTokenAny([
            TokenType::T_INTEGER,
            TokenType::T_STRING,
            TokenType::T_FLOAT,
            TokenType::T_COMMA,
        ])) {
            if ($this->lexer->isNextTokenAny([TokenType::T_COMMA])) {
                $this->match($this->lexer->lookahead->type);

                continue;
            }

            $this->match($this->lexer->lookahead->type);

            $value[] = $this->lexer->token->value;
        }

        $this->match(TokenType::T_CLOSE_PARENTHESIS);

        return $value;
    }

    private function extractIdentifier(): string
    {
        $this->match(TokenType::T_IDENTIFIER);
        $identifier = $this->lexer->token->value;

        if (! \is_string($identifier)) {
            $this->syntaxError('Identifier must be a string');
        }

        return $identifier;
    }

    private function extractOperator(): string
    {
        $operator = null;

        while ($this->lexer->isNextTokenAny([
            TokenType::T_GREATER_THAN,
            TokenType::T_LOWER_THAN,
            TokenType::T_NOT,
            TokenType::T_NEGATE,
            TokenType::T_EQUALS,
            TokenType::T_LIKE,
            TokenType::T_IN,
        ])) {
            $this->match($this->lexer->lookahead->type);
            $operator .= $this->lexer->token->value;
        }

        if ($operator === null) {
            $this->syntaxError('Any operator');
        }

        return $operator;
    }

    /**
     * @return Expression[]
     */
    private function extractExpressions(TokenType $tokenType): array
    {
        $expressions = [];
        while ($this->lexer->isNextTokenAny([TokenType::T_OPEN_PARENTHESIS, TokenType::T_AND])) {
            switch ($this->lexer->lookahead->type) {
                case TokenType::T_OPEN_PARENTHESIS:
                    $expressions[] = $this->extractComparison();

                    break;
                case TokenType::T_AND:
                    $this->match(TokenType::T_AND);

                    break;
            }
        }

        return $expressions;
    }

    private function match(TokenType $token): void
    {
        $lookaheadType = $this->lexer->lookahead->type ?? null;

        // Short-circuit on first condition, usually types match
        if ($lookaheadType === $token) {
            $this->lexer->moveNext();

            return;
        }

        // If parameter is not identifier (1-99) must be exact match
        if ($token->value < TokenType::T_IDENTIFIER->value) {
            $this->syntaxError($this->lexer->getLiteral($token));
        }

        // If parameter is keyword (200+) must be exact match
        if ($token->value > TokenType::T_IDENTIFIER->value) {
            $this->syntaxError($this->lexer->getLiteral($token));
        }

        // If parameter is T_IDENTIFIER, then matches T_IDENTIFIER (100) and keywords (200+)
        if ($token->value === TokenType::T_IDENTIFIER->value && $lookaheadType->value < TokenType::T_IDENTIFIER->value) {
            $this->syntaxError($this->lexer->getLiteral($token));
        }

        $this->lexer->moveNext();
    }

    public function syntaxError(string $expected = '', ?Token $token = null): never
    {
        if ($token === null) {
            $token = $this->lexer->lookahead;
        }

        $tokenPos = $token->position ?? '-1';

        $message = \sprintf('line 0, col %d: Error: ', $tokenPos);
        $message .= $expected !== '' ? \sprintf('Expected %s, got ', $expected) : 'Unexpected ';
        $message .= $this->lexer->lookahead === null ? 'end of string.' : \sprintf('\'%s\'', $token->value);

        throw new \RuntimeException(\sprintf('[Syntax Error] %s.', $message));
    }
}
