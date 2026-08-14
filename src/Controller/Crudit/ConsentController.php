<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Controller\Crudit;

use Lle\CruditBundle\Controller\AbstractCrudController;
use Lle\CruditBundle\Controller\TraitCrudController;
use Lle\HermesBundle\Crudit\Config\ConsentCrudConfig;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/consent')]
class ConsentController extends AbstractCrudController
{
    use TraitCrudController;

    public function __construct(ConsentCrudConfig $config)
    {
        $this->config = $config;
    }
}
