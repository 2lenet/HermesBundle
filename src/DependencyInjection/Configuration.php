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
        $children->scalarNode('root_dir')->isRequired()->end();
        $children->scalarNode('upload_path')->defaultValue('/upload/images/')->end();
        $children->scalarNode('app_secret')->isRequired()->end();
        $children->scalarNode('app_domain')->isRequired()->end();
        $children->scalarNode('bounce_email')->isRequired()->end();
        $children->scalarNode('bounce_pass')->isRequired()->end();
        $children->scalarNode('bounce_host')->isRequired()->end();
        $children->scalarNode('menu_icons')->defaultTrue()->end();

        return $treeBuilder;
    }
}
