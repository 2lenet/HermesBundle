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
        $children->scalarNode('bounce_host')->isRequired()->end();
        $children->scalarNode('bounce_port')->isRequired()->end();
        $children->scalarNode('bounce_user')->isRequired()->end();
        $children->scalarNode('bounce_password')->isRequired()->end();
        $children->scalarNode('menu_icons')->defaultTrue()->end();
        $children->scalarNode('recipient_error_retry')->defaultValue(3)->end();

        return $treeBuilder;
    }
}
