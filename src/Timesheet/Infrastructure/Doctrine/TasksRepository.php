<?php

declare(strict_types=1);

namespace Smoothie\ContractorTools\Timesheet\Infrastructure\Doctrine;

use Doctrine\DBAL\Schema\Schema as DbalSchema;
use Smoothie\ContractorTools\Contracts\Schema;

class TasksRepository implements Schema
{
    public function specifySchema(DbalSchema $schema): void
    {
        $woot = $schema->createTable('woot');
        $woot->addColumn('id', 'integer', [
            'autoincrement' => true,
        ]);
        $woot->addColumn('baum', 'string', [
            'notnull' => false,
        ]);
        $woot->setPrimaryKey(['id']);
    }
}
