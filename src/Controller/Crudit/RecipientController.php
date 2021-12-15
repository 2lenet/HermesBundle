<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Controller\Crudit;

use Lle\CruditBundle\Controller\AbstractCrudController;
use Lle\CruditBundle\Controller\TraitCrudController;
use Lle\HermesBundle\Crudit\Config\RecipientCrudConfig;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/recipient")
 */
class RecipientController extends AbstractCrudController
{
    use TraitCrudController;

    public function __construct(RecipientCrudConfig $config)
    {
        $this->config = $config;
    }
}
