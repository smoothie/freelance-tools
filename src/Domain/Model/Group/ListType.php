<?php

declare(strict_types=1);

namespace App\Domain\Model\Group;

enum ListType: string
{
    case DAYS = 'days';
    case UNKNOWN = 'unknown';
}
