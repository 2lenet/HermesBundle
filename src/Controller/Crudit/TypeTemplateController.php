<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Controller\Crudit;

use Lle\CruditBundle\Controller\AbstractCrudController;
use Lle\CruditBundle\Controller\TraitCrudController;
use Lle\HermesBundle\Crudit\Config\TypeTemplateCrudConfig;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/typetemplate')]
class TypeTemplateController extends AbstractCrudController
{
    use TraitCrudController;

    public function __construct(TypeTemplateCrudConfig $config)
    {
        $this->config = $config;
    }
}
