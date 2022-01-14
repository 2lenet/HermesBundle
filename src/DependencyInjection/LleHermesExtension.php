<?php

namespace Lle\HermesBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
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
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $processedConfig = $this->processConfiguration($configuration, $configs);

        $container->setParameter('lle_hermes.root_dir', $processedConfig['root_dir']);
        $container->setParameter('lle_hermes.app_secret', $processedConfig['app_secret']);
        $container->setParameter('lle_hermes.app_domain', $processedConfig['app_domain']);
        $container->setParameter('lle_hermes.bounce_email', $processedConfig['bounce_email']);
        $container->setParameter('lle_hermes.bounce_pass', $processedConfig['bounce_pass']);
        $container->setParameter('lle_hermes.bounce_host', $processedConfig['bounce_host']);

        // Load Hermes' form types
        if ($container->hasParameter("twig.form.resources")) {
            $container->setParameter("twig.form.resources", array_merge(
                ["@LleHermes/form/custom_types.html.twig"],
                $container->getParameter("twig.form.resources")
            ));
        }
    }
}
