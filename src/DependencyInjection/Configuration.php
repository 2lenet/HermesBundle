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
            ->end()
            ->arrayNode('mail_twig')
                ->info('Twig environment used to render mail templates written from the back office.')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('extensions')
                        ->info('Service ids of the project Twig extensions mail templates may use.')
                        ->scalarPrototype()->end()
                        ->defaultValue([])
                    ->end()
                    ->arrayNode('sandbox')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->booleanNode('enabled')
                                ->defaultTrue()
                            ->end()
                            ->arrayNode('allowed_tags')
                                ->info('Added to MailSecurityPolicyFactory::DEFAULT_TAGS.')
                                ->scalarPrototype()->end()
                                ->defaultValue([])
                            ->end()
                            ->arrayNode('allowed_filters')
                                ->info('Added to MailSecurityPolicyFactory::DEFAULT_FILTERS.')
                                ->scalarPrototype()->end()
                                ->defaultValue([])
                            ->end()
                            ->arrayNode('allowed_functions')
                                ->info('Added to MailSecurityPolicyFactory::DEFAULT_FUNCTIONS.')
                                ->scalarPrototype()->end()
                                ->defaultValue([])
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
