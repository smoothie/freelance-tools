<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Model\FilterCriteria;
use App\Domain\Model\FilterExpressions;

interface FilterQueryParser
{
    public function parse(FilterCriteria $query): FilterExpressions;
}
