<?php

namespace Lle\HermesBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Lle\HermesBundle\DependencyInjection
 *
 * @author 2LE <2le@2le.net>
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('lle_hermes');
        $rootNode = $treeBuilder->getRootNode();
        $children = $rootNode->children();
        /** @phpstan-ignore-next-line */
        $children
            ->scalarNode('root_dir')
                ->isRequired()
            ->end()
            ->scalarNode('attachment_path')
                ->defaultValue('/data/hermes/attachments/')
            ->end()
            ->scalarNode('upload_path')
                ->defaultValue('/upload/images/')
            ->end()
            ->scalarNode('app_secret')
                ->isRequired()
            ->end()
            ->scalarNode('app_domain')
                ->isRequired()
            ->end()
            ->scalarNode('bounce_host')
                ->isRequired()
            ->end()
            ->scalarNode('bounce_port')
                ->isRequired()
            ->end()
            ->scalarNode('bounce_user')
                ->isRequired()
            ->end()
            ->scalarNode('bounce_password')
                ->isRequired()
            ->end()
            ->scalarNode('menu_icons')
                ->defaultTrue()
            ->end()
            ->scalarNode('recipient_error_retry')
                ->defaultValue(3)
            ->end()
            ->scalarNode('tenant_class')
                ->defaultNull()
            ->end()
            ->scalarNode('attachment_nb_days_before_deletion')
                ->defaultValue(365)
            ->end();

        return $treeBuilder;
    }
}
