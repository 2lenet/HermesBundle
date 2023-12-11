<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Controller\Crudit;

use Lle\CruditBundle\Controller\AbstractCrudController;
use Lle\CruditBundle\Controller\TraitCrudController;
use Lle\HermesBundle\Crudit\Config\EmailErrorCrudConfig;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/emailerror')]
class EmailErrorController extends AbstractCrudController
{
    use TraitCrudController;

    public function __construct(EmailErrorCrudConfig $config)
    {
        $this->config = $config;
    }
}
