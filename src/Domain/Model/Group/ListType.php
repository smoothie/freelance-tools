<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Domain\Model\Group;

enum ListType: string
{
    case DAYS = 'days';
    case UNKNOWN = 'unknown';
}
