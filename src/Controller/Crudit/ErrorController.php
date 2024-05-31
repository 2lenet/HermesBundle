<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Controller\Crudit;

use Lle\CruditBundle\Controller\AbstractCrudController;
use Lle\CruditBundle\Controller\TraitCrudController;
use Lle\HermesBundle\Crudit\Config\ErrorCrudConfig;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/error')]
class ErrorController extends AbstractCrudController
{
    use TraitCrudController;

    public function __construct(ErrorCrudConfig $config)
    {
        $this->config = $config;
    }
}
