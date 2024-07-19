<?php

declare(strict_types=1);

namespace Smoothie\ContractorTools\Symfony\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DoctrineMigrationsCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->has('doctrine.migrations')) {
            throw new \RuntimeException('"smoothie.ContractorTools" requires "DoctrineMigrationsBundle" to be enabled.');
        }

        $definition = $container->getDefinition('doctrine.migrations.dependency_factory');
        $definition->addMethodCall(
            'setDefinition',
            [
                'Doctrine\Migrations\Provider\SchemaProvider',
                new ServiceClosureArgument(new Reference('smoothie.contractor-tools.schema-provider')),
            ],
        );
    }
}
