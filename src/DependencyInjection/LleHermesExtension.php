<?php

namespace Lle\HermesBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

/**
 * Class LleHermesExtension
 * @package Lle\HermesBundle\DependencyInjection
 *
 * @author 2LE <2le@2le.net>
 */
class LleHermesExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $processedConfig = $this->processConfiguration($configuration, $configs);

        $container->setParameter('lle_hermes.root_dir', $processedConfig['root_dir']);
        $container->setParameter('lle_hermes.attachment_path', $processedConfig['attachment_path']);
        $container->setParameter('lle_hermes.upload_path', $processedConfig['upload_path']);
        $container->setParameter('lle_hermes.app_secret', $processedConfig['app_secret']);
        $container->setParameter('lle_hermes.app_domain', $processedConfig['app_domain']);
        $container->setParameter('lle_hermes.bounce_host', $processedConfig['bounce_host']);
        $container->setParameter('lle_hermes.bounce_port', $processedConfig['bounce_port']);
        $container->setParameter('lle_hermes.bounce_user', $processedConfig['bounce_user']);
        $container->setParameter('lle_hermes.bounce_password', $processedConfig['bounce_password']);
        $container->setParameter('lle_hermes.menu_icons', $processedConfig['menu_icons']);
        $container->setParameter('lle_hermes.recipient_error_retry', $processedConfig['recipient_error_retry']);
        $container->setParameter('lle_hermes.tenant_class', $processedConfig['tenant_class']);
        $container->setParameter(
            'lle_hermes.attachment_nb_days_before_deletion',
            $processedConfig['attachment_nb_days_before_deletion']
        );

        // Load the templates for the Hermes form types
        if ($container->hasParameter('twig.form.resources')) {
            /** @var array $parameter */
            $parameter = $container->getParameter('twig.form.resources');

            $container->setParameter(
                'twig.form.resources',
                array_merge(
                    ['@LleHermes/form/custom_types.html.twig'],
                    $parameter
                )
            );
        }
    }

    public function prepend(ContainerBuilder $container): void
    {
        $hasDoctrineTranslatable = false;
        $hasStofDoctrineExtensions = false;
        foreach ($container->getExtensionConfig('doctrine') as $config) {
            if (isset($config['orm']['mappings']['translatable'])) {
                $hasDoctrineTranslatable = true;
                break;
            }
        }

        if (!$hasDoctrineTranslatable) {
            $container->prependExtensionConfig('doctrine', [
                'orm' => [
                    'mappings' => [
                        'translatable' => [
                            'is_bundle' => false,
                            'type' => 'attribute',
                            'prefix' => 'Gedmo\Translatable\Entity',
                            'dir' => '%kernel.project_dir%/vendor/gedmo/doctrine-extensions/src/Translatable/Entity',
                            'alias' => 'GedmoTranslatable',
                        ],
                    ],
                ],
            ]);
        }

        foreach ($container->getExtensionConfig('stof_doctrine_extensions') as $config) {
            if (
                isset($config['orm']['default']['translatable'])
                && isset($config['default_locale'])
                && isset($config['translation_fallback'])
                && isset($config['class'])
            ) {
                $hasStofDoctrine_extensions = true;
                break;
            }
        }

        if (!$hasStofDoctrineExtensions) {
            $container->prependExtensionConfig('stof_doctrine_extensions', [
                'default_locale' => 'en_US',
                'translation_fallback' => true,
                'orm' => [
                    'default' => [
                        'translatable' => [
                            'tree' => true,
                            'timestampable' => true,
                            'blameable' => true,
                            'loggable' => true,
                        ],
                    ],
                ],
                'class' => [
                    'loggable' => 'App\EventListener\CustomLoggableListener',
                ],
            ]);
        }

        $container->prependExtensionConfig("lle_entity_file", [
            "configurations" => [
                "attached_file" => [
                    "class" => "Lle\\HermesBundle\\Entity\\Template",
                    "storage_adapter" => "lle_entity_file.storage.default",
                    "role" => "PUBLIC_ACCESS",
                ],
                "mail_attached_file" => [
                    "class" => "Lle\\HermesBundle\\Entity\\Mail",
                    "storage_adapter" => "lle_entity_file.storage.default",
                    "role" => "ROLE_HERMES_MAIL_READATTACHMENT",
                ],
            ],
        ]);
    }
}
