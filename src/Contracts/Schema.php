<?php

declare(strict_types=1);

namespace Smoothie\ContractorTools\Contracts;

use Doctrine\DBAL\Schema\Schema as DbalSchema;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('smoothie.contractor-tools.schemas')]
interface Schema
{
    public function specifySchema(DbalSchema $schema): void;
}
