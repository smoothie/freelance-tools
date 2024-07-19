<?php

declare(strict_types=1);

namespace Smoothie\ContractorTools\Tests;

use PHPUnit\Framework\Attributes\After;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as SymfonyWebTestCase;

class WebTestCase extends SymfonyWebTestCase implements PathsForTesting, Snapshots
{
    use ProvidesPathsForTesting;
    use ProvidesSnapshots;

    /**
     * Ensures we clean up the error handler while shutdown.
     *
     * @see https://github.com/symfony/symfony/issues/53812
     */
    #[After]
    public function __internalDisableErrorHandler(): void
    {
        restore_exception_handler();
    }
}
