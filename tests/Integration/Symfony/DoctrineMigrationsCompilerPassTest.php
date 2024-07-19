<?php

declare(strict_types=1);

namespace Smoothie\ContractorTools\Tests\Integration\Symfony;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Smoothie\ContractorTools\Doctrine\SchemaProvider;
use Smoothie\ContractorTools\Symfony\DependencyInjection\Compiler\DoctrineMigrationsCompilerPass;
use Smoothie\ContractorTools\Tests\KernelTestCase;

#[Group('integration')]
#[Group('integration-doctrine')]
#[Group('integration-doctrine-migrations-compiler-pass')]
#[CoversClass(DoctrineMigrationsCompilerPass::class)]
class DoctrineMigrationsCompilerPassTest extends KernelTestCase
{
    public function testThatWeHaveAttachedOurSchemaProvider(): void
    {
        static::bootKernel();
        $container = static::getContainer();

        $dependencyFactory = $container->get('doctrine.migrations.dependency_factory');
        self::assertTrue($dependencyFactory->hasSchemaProvider());
        self::assertInstanceOf(SchemaProvider::class, $dependencyFactory->getSchemaProvider());
    }
}
