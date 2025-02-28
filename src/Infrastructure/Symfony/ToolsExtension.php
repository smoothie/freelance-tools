<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class ToolsExtension extends Extension implements ConfigurationInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this, $configs);
        $container->setParameter('tools.default_author', 'smoothie <hello@marceichenseher.de>');

        $this->applyOrganizations($config, $container);
        $this->applyToggle($container);
        $this->applyDomPdf($config, $container);
        $this->applyDefaults($config, $container);
    }

    /**
     * @param array<string, array<string, mixed>> $config
     */
    private function applyOrganizations(array $config, ContainerBuilder $container): void
    {
        if (! \array_key_exists('organizations', $config)) {
            return;
        }

        $container->setParameter('tools.organizations', $config['organizations']);
    }

    private function applyToggle(ContainerBuilder $container): void
    {
        $container->setParameter('tools.default_toggl_api_url', 'https://api.track.toggl.com/api/v9/');
        $container->setParameter(
            'tools.default_toggl_report_url',
            'https://api.track.toggl.com/reports/api/v3/workspace/',
        );
        $container->setParameter('tools.toggl_api_key', '%env(string:TOOLS_TOGGL_API_KEY)%');
        $container->setParameter(
            'tools.toggl_api_url',
            '%env(default:tools.default_toggl_api_url:string:TOOLS_TOGGL_API_URL)%',
        );
        $container->setParameter(
            'tools.toggl_report_url',
            '%env(default:tools.default_toggl_report_url:string:TOOLS_TOGGL_REPORT_URL)%',
        );
        $container->setParameter('tools.toggl_workspace_id', '%env(int:TOOLS_TOGGL_WORKSPACE_ID)%');
    }

    /**
     * @param array<string, array<string, mixed>> $config
     */
    private function applyDomPdf(array $config, ContainerBuilder $container): void
    {
        if (! \array_key_exists('dompdf', $config)) {
            return;
        }

        foreach ($config['dompdf'] as $name => $value) {
            if (! \in_array(
                $name,
                [
                    'creator',
                    'producer',
                    'exportDirectory',
                    'fontsDirectory',
                    'templateDirectory',
                    'tmpDirectory',
                    'fonts',
                ],
                true,
            )) {
                continue;
            }

            $container->setParameter('tools.dompdf_'.$name, $value);
        }
    }

    /**
     * @param array<string, array<string, mixed>> $config
     */
    private function applyDefaults(array $config, ContainerBuilder $container): void
    {
        if (! \array_key_exists('default', $config)) {
            return;
        }

        foreach ($config['default'] as $name => $value) {
            if (! \in_array(
                $name,
                ['providedBy'],
                true,
            )) {
                continue;
            }

            $container->setParameter('tools.default_'.$name, $value);
        }
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('tools');

        $treeBuilder
            ->getRootNode()
            ->children()
            ->arrayNode('default')->addDefaultsIfNotSet()
            ->children()
            ->arrayNode('providedBy')->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('name')->defaultValue('%env(string:TOOLS_PROVIDED_BY_NAME)%')->cannotBeEmpty()->end()
            ->scalarNode('street')->defaultValue('%env(string:TOOLS_PROVIDED_BY_STREET)%')->cannotBeEmpty()->end()
            ->scalarNode('location')->defaultValue('%env(string:TOOLS_PROVIDED_BY_LOCATION)%')->cannotBeEmpty()->end()
            ->scalarNode('vatId')->defaultValue('%env(string:TOOLS_PROVIDED_BY_VATID)%')->cannotBeEmpty()->end()
            ->scalarNode('country')->defaultValue('%env(string:TOOLS_PROVIDED_BY_COUNTRY)%')->cannotBeEmpty()->end()
            ->scalarNode('phone')->defaultValue('%env(string:TOOLS_PROVIDED_BY_PHONE)%')->cannotBeEmpty()->end()
            ->scalarNode('mail')->defaultValue('%env(string:TOOLS_PROVIDED_BY_MAIL)%')->cannotBeEmpty()->end()
            ->scalarNode('web')->defaultValue('%env(string:TOOLS_PROVIDED_BY_WEB)%')->cannotBeEmpty()->end()
            ->scalarNode('bank')->defaultValue('%env(string:TOOLS_PROVIDED_BY_BANK)%')->cannotBeEmpty()->end()
            ->scalarNode('iban')->defaultValue('%env(string:TOOLS_PROVIDED_BY_IBAN)%')->cannotBeEmpty()->end()
            ->scalarNode('bic')->defaultValue('%env(string:TOOLS_PROVIDED_BY_BIC)%')->cannotBeEmpty()->end()
            ->end()
            ->end();

        $treeBuilder
            ->getRootNode()
            ->children()
            ->arrayNode('dompdf')
            ->children()
            ->scalarNode('creator')->defaultValue('%env(string:TOOLS_PROVIDED_BY_NAME)%')->cannotBeEmpty()->end()
            ->scalarNode('producer')->defaultValue(
                '%env(default:tools.default_author:string:TOOLS_AUTHOR)%',
            )->cannotBeEmpty()->end()
            ->scalarNode('exportDirectory')->defaultValue('%kernel.project_dir%/var/pdf/')->cannotBeEmpty()->end()
            ->scalarNode('fontsDirectory')->defaultValue('%twig.default_path%/fonts/')->cannotBeEmpty()->end()
            ->scalarNode('templateDirectory')->defaultValue('%twig.default_path%/pdf/')->cannotBeEmpty()->end()
            ->scalarNode('tmpDirectory')->defaultValue(sys_get_temp_dir().'/')->cannotBeEmpty()->end()
            ->arrayNode('fonts')->arrayPrototype()
            ->children()
            ->scalarNode('file')->isRequired()->cannotBeEmpty()->end()
            ->arrayNode('style')->isRequired()
            ->children()
            ->scalarNode('family')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('style')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('weight')->isRequired()->cannotBeEmpty()->end()
            ->end()
            ->end()
            ->end()
            ->end();

        $treeBuilder
            ->getRootNode()
            ->children()
            ->arrayNode('organizations')->arrayPrototype()->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('project')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('name')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('street')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('location')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('country')->defaultValue('DE')->cannotBeEmpty()->end()
            ->scalarNode('vatId')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('description')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('taxRate')->defaultValue(19.0)->cannotBeEmpty()->end()
            ->scalarNode('pricePerHour')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('termOfPaymentInDays')->defaultValue(30)->cannotBeEmpty()->end()
            ->end()
            ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
