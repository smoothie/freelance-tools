<?php

declare(strict_types=1);

namespace Smoothie\ContractorTools\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as SymfonyKernelTestCase;

class KernelTestCase extends SymfonyKernelTestCase implements PathsForTesting, Snapshots
{
    use ProvidesPathsForTesting;
    use ProvidesSnapshots;

    /**
     * Ensures we clean up the error handler while shutdown.
     *
     * @see https://github.com/symfony/symfony/issues/53812
     */
    protected static function ensureKernelShutdown(): void
    {
        $wasBooted = static::$booted;
        parent::ensureKernelShutdown();

        if ($wasBooted) {
            restore_exception_handler();
        }
    }
}
