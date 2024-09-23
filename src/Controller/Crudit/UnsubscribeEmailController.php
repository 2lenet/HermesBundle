<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Controller\Crudit;

use Lle\CruditBundle\Controller\AbstractCrudController;
use Lle\CruditBundle\Controller\TraitCrudController;
use Lle\HermesBundle\Crudit\Config\UnsubscribeEmailCrudConfig;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/unsubscribeemail')]
class UnsubscribeEmailController extends AbstractCrudController
{
    use TraitCrudController;

    public function __construct(UnsubscribeEmailCrudConfig $config)
    {
        $this->config = $config;
    }
}
