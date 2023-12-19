<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Controller\Crudit;

use Lle\CruditBundle\Brick\BrickResponse\FlashBrickResponse;
use Lle\CruditBundle\Brick\BrickResponseCollector;
use Lle\CruditBundle\Builder\BrickBuilder;
use Lle\CruditBundle\Contracts\CrudConfigInterface;
use Lle\CruditBundle\Controller\AbstractCrudController;
use Lle\CruditBundle\Controller\TraitCrudController;
use Lle\HermesBundle\Crudit\Config\RecipientCrudConfig;
use Lle\HermesBundle\Entity\Recipient;
use Lle\HermesBundle\Service\MultiTenantManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/recipient')]
class RecipientController extends AbstractCrudController
{
    use TraitCrudController {
        TraitCrudController::show as traitShow;
    }

    public function __construct(
        RecipientCrudConfig $config,
        protected readonly MultiTenantManager $multiTenantManager,
    ) {
        $this->config = $config;
    }

    #[Route('/show/{id}')]
    public function show(Request $request, Recipient $recipient): Response
    {
        if (!$this->multiTenantManager->isOwner($recipient)) {
            $this->addFlash(FlashBrickResponse::ERROR, 'flash.not_owner.recipient');

            return $this->redirectToRoute($this->config->getRootRoute() . '_index');
        }

        return $this->traitShow($request, $recipient);
    }
}
