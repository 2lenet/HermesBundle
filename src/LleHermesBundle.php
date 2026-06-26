<?php

namespace Lle\HermesBundle;

use Lle\HermesBundle\DependencyInjection\Compiler\ExcludeTemplateMappingPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class LleHermesBundle
 * @package Lle\HermesBundle
 *
 * @author 2LE <2le@2le.net>
 */
class LleHermesBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new ExcludeTemplateMappingPass());
    }
}
