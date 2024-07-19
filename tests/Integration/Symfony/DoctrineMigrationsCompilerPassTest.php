<?php

declare(strict_types=1);

namespace Smoothie\ContractorTools\Tests\Integration\Symfony;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\DB2Platform;
use Doctrine\DBAL\Platforms\MariaDBPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\OraclePlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Platforms\SQLServerPlatform;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
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

    #[DataProvider('provideSupportedPlatforms')]
    public function testThatWeCanGenerateMigrationsForSupportedPlatforms(AbstractPlatform $platform): void
    {
        static::bootKernel();
        $container = static::getContainer();

        $dependencyFactory = $container->get('doctrine.migrations.dependency_factory');
        /** @var SchemaProvider $schemaProvider */
        $schemaProvider = $dependencyFactory->getSchemaProvider();
        $schema = $schemaProvider->createSchema();

        $this->assertMatchesSnapshot($schema->toSql($platform));
    }

    public static function provideSupportedPlatforms(): \Generator
    {
        yield 'sqlite' => ['platform' => new SqlitePlatform()];
        yield 'PostgreSQL' => ['platform' => new PostgreSQLPlatform()];
        yield 'MariaDB' => ['platform' => new MariaDBPlatform()];
        yield 'MySQL' => ['platform' => new MySQLPlatform()];
        yield 'DB2' => ['platform' => new DB2Platform()];
        yield 'Oracle' => ['platform' => new OraclePlatform()];
        yield 'SQLServer' => ['platform' => new SQLServerPlatform()];
    }
}
