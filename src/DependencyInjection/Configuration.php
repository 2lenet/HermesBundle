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
        $rootNode
            ->children()
            ->scalarNode('root_dir')->isRequired()->end()
            ->scalarNode('app_secret')->isRequired()->end()
            ->scalarNode('app_domain')->isRequired()->end()
            ->scalarNode('bounce_email')->isRequired()->end()
            ->scalarNode('bounce_pass')->isRequired()->end()
            ->scalarNode('bounce_host')->isRequired()->end();
        return $treeBuilder;
    }
}
