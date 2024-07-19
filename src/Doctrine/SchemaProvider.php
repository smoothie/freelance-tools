<?php

declare(strict_types=1);

namespace Smoothie\ContractorTools\Doctrine;

use Doctrine\DBAL\Schema\Schema as DbalSchema;
use Doctrine\Migrations\Provider\SchemaProvider as DbalSchemaProvider;
use Smoothie\ContractorTools\Contracts\Schema;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

#[AsAlias('smoothie.contractor-tools.schema-provider', true)]
class SchemaProvider implements DbalSchemaProvider
{
    /**
     * @param iterable<Schema> $schemas
     */
    public function __construct(
        #[AutowireIterator('smoothie.contractor-tools.schemas')]
        private iterable $schemas,
    ) {
    }

    public function createSchema(): DbalSchema
    {
        $db = new DbalSchema();
        foreach ($this->schemas as $schema) {
            $schema->specifySchema($db);
        }

        return $db;
    }
}
