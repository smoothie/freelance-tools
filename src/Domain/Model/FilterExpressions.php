<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Domain\Model;

use Webmozart\Assert\Assert;

class FilterExpressions
{
    /**
     * @param list<mixed> $expressions
     */
    public function __construct(private array $expressions)
    {
        Assert::allIsInstanceOf($expressions, Expression::class);
    }

    public function startDate(): Expression
    {
        return $this->getByFieldName('startDate');
    }

    public function endDate(): Expression
    {
        return $this->getByFieldName('endDate');
    }

    public function duration(): Expression
    {
        return $this->getByFieldName('duration');
    }

    public function client(): Expression
    {
        return $this->getByFieldName('client');
    }

    public function project(): Expression
    {
        return $this->getByFieldName('project');
    }

    public function tag(): Expression
    {
        return $this->getByFieldName('tag');
    }

    public function description(): Expression
    {
        return $this->getByFieldName('description');
    }

    /**
     * @return Expression[]
     */
    public function toArray(): array
    {
        return $this->expressions;
    }

    private function getByFieldName(string $fieldName): Expression
    {
        foreach ($this->expressions as $expression) {
            if ($expression->fieldName() === $fieldName) {
                return $expression;
            }
        }

        return new EmptyExpression();
    }
}
