<?php

declare(strict_types=1);

namespace Smoothie\ContractorTools\Contracts;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

#[AutoconfigureTag('smoothie.contractor-tools.compiler-passes')]
interface CompilerPass extends CompilerPassInterface
{
}
