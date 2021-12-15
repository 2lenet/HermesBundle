<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Controller\Crudit;

use Lle\CruditBundle\Controller\AbstractCrudController;
use Lle\CruditBundle\Controller\TraitCrudController;
use Lle\HermesBundle\Crudit\Config\TemplateCrudConfig;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/template")
 */
class TemplateController extends AbstractCrudController
{
    use TraitCrudController;

    public function __construct(TemplateCrudConfig $config)
    {
        $this->config = $config;
    }
}
