<?php

declare(strict_types=1);

namespace Smoothie\ContractorTools\Tests;

use PHPUnit\Framework\TestCase;

class BasicTestCase extends TestCase implements PathsForTesting, Snapshots
{
    use ProvidesPathsForTesting;
    use ProvidesSnapshots;
}
