<?php

namespace Lle\HermesBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class LleHermesExtension
 * @package Lle\HermesBundle\DependencyInjection
 *
 * @author 2LE <2le@2le.net>
 */
class LleHermesExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $processedConfig = $this->processConfiguration($configuration, $configs);

        $container->setParameter('lle_hermes.root_dir', $processedConfig['root_dir']);
        $container->setParameter('lle_hermes.upload_path', $processedConfig['upload_path']);
        $container->setParameter('lle_hermes.app_secret', $processedConfig['app_secret']);
        $container->setParameter('lle_hermes.app_domain', $processedConfig['app_domain']);
        $container->setParameter('lle_hermes.bounce_email', $processedConfig['bounce_email']);
        $container->setParameter('lle_hermes.bounce_pass', $processedConfig['bounce_pass']);
        $container->setParameter('lle_hermes.bounce_host', $processedConfig['bounce_host']);
        $container->setParameter('lle_hermes.menu_icons', $processedConfig['menu_icons']);

        // Load Hermes' form types
        if ($container->hasParameter("twig.form.resources")) {
            /** @var array $twigFormResources */
            $twigFormResources = $container->getParameter('twig.form.resources');
            $container->setParameter(
                "twig.form.resources",
                array_merge(['@LleHermes/form/custom_types.html.twig'], $twigFormResources)
            );
        }
    }
}
