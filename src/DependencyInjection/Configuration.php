<?php

namespace Lle\HermesBundle\DependencyInjection;

use Lle\HermesBundle\Crudit\CrudMenu\HermesMenuProvider;
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
            ->booleanNode('menu_icons')
                ->defaultTrue()
                // Used to be a scalar node, so a quoted `'false'` was accepted (and read as true). Such strings are
                // now read as the boolean they spell; anything else (an env placeholder, a real boolean) is untouched.
                ->beforeNormalization()
                    ->ifString()
                    ->then(static function (string $value): bool|string {
                        $parsed = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

                        return $parsed ?? $value;
                    })
                ->end()
            ->end()
            ->enumNode('menu_icon_style')
                ->values([HermesMenuProvider::ICON_STYLE_IMAGE, HermesMenuProvider::ICON_STYLE_FONTAWESOME])
                ->defaultValue(HermesMenuProvider::ICON_STYLE_IMAGE)
            ->end()
            ->scalarNode('recipient_error_retry')
                ->defaultValue(3)
            ->end()
            ->scalarNode('recipient_max_retry')
                ->defaultValue(3)
            ->end()
            ->scalarNode('tenant_class')
                ->defaultNull()
            ->end()
            ->scalarNode('attachment_nb_days_before_deletion')
                ->defaultValue(365)
            ->end()
            ->booleanNode('translatable_mail')
                ->defaultTrue()
            ->end();

        return $treeBuilder;
    }
}
