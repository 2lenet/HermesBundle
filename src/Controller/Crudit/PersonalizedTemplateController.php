<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Controller\Crudit;

use Lle\CruditBundle\Controller\AbstractCrudController;
use Lle\CruditBundle\Controller\TraitCrudController;
use Lle\HermesBundle\Crudit\Config\PersonalizedTemplateCrudConfig;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/template/custom')]
class PersonalizedTemplateController extends AbstractCrudController
{
    use TraitCrudController;

    public function __construct(
        PersonalizedTemplateCrudConfig $config,
    ) {
        $this->config = $config;
    }
}
