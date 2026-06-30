<?php

namespace Lle\HermesBundle\DependencyInjection\Compiler;

use Lle\HermesBundle\Entity\Template;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ExcludeTemplateMappingPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->getParameter('lle_hermes.translatable_mail')) {
            return;
        }

        $templatePath = (new \ReflectionClass(Template::class))->getFileName();
        if ($templatePath === false) {
            return;
        }

        foreach ($container->getDefinitions() as $id => $chainDef) {
            if (!str_ends_with($id, '_metadata_driver')) {
                continue;
            }

            foreach ($chainDef->getMethodCalls() as [$method, $args]) {
                if ($method !== 'addDriver') {
                    continue;
                }

                $namespace = $args[1] ?? null;
                if ($namespace !== 'Lle\\HermesBundle\\Entity') {
                    continue;
                }

                $driverRef = $args[0];
                if ($driverRef instanceof Reference) {
                    $container->getDefinition((string) $driverRef)
                        ->addMethodCall('addExcludePaths', [[$templatePath]]);
                }
            }
        }
    }
}
