<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Domain\Model;

use Smoothie\FreelanceTools\Domain\Model\Common\UsesUuid;
use Smoothie\FreelanceTools\Domain\Model\Common\Uuid;

class TimesheetReportId implements Uuid
{
    use UsesUuid;
}
