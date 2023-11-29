<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Controller\Crudit;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CruditBundle\Controller\AbstractCrudController;
use Lle\CruditBundle\Controller\TraitCrudController;
use Lle\HermesBundle\Crudit\Config\PersonalizedTemplateCrudConfig;
use Lle\HermesBundle\Repository\TemplateRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

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
