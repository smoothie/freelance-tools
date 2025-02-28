<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Domain\Service;

use Smoothie\FreelanceTools\Domain\Model\FilterCriteria;
use Smoothie\FreelanceTools\Domain\Model\FilterExpressions;

interface FilterQueryParser
{
    public function parse(FilterCriteria $query): FilterExpressions;
}
