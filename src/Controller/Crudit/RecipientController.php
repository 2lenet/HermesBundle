<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Controller\Crudit;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CruditBundle\Brick\BrickResponse\FlashBrickResponse;
use Lle\CruditBundle\Controller\AbstractCrudController;
use Lle\CruditBundle\Controller\TraitCrudController;
use Lle\HermesBundle\Crudit\Config\RecipientCrudConfig;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Entity\Recipient;
use Lle\HermesBundle\Service\Factory\RecipientFactory;
use Lle\HermesBundle\Service\MultiTenantManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/recipient')]
class RecipientController extends AbstractCrudController
{
    use TraitCrudController {
        TraitCrudController::show as traitShow;
    }

    public function __construct(
        RecipientCrudConfig $config,
        protected EntityManagerInterface $em,
        protected MultiTenantManager $multiTenantManager,
        protected RecipientFactory $recipientFactory,
    ) {
        $this->config = $config;
    }

    #[Route('/show/{id}')]
    public function show(Request $request, Recipient $recipient): Response
    {
        $this->denyAccessUnlessGranted('ROLE_HERMES_RECIPIENT_SHOW');

        if (!$this->multiTenantManager->isOwner($recipient)) {
            $this->addFlash(FlashBrickResponse::ERROR, 'flash.not_owner.recipient');

            return $this->redirectToRoute($this->config->getRootRoute() . '_index');
        }

        return $this->traitShow($request, $recipient);
    }

    #[Route('/resend/{id}', name: 'lle_hermes_crudit_recipient_resend')]
    public function resend(Recipient $recipient): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_HERMES_RECIPIENT_RESEND');

        if (!$this->multiTenantManager->isOwner($recipient)) {
            $this->addFlash(FlashBrickResponse::ERROR, 'flash.not_owner.recipient');

            return $this->redirectToRoute($this->config->getRootRoute() . '_index');
        }

        $mail = $recipient->getMail();
        if (!$mail) {
            $this->addFlash(FlashBrickResponse::ERROR, 'flash.no_mail');

            return $this->redirectToRoute($this->config->getRootRoute() . '_index');
        }

        if (!$this->multiTenantManager->isOwner($mail)) {
            $this->addFlash(FlashBrickResponse::ERROR, 'flash.not_owner.mail');

            return $this->redirectToRoute($this->config->getRootRoute() . '_index');
        }

        $copy = $this->recipientFactory->copy($recipient);
        $this->em->persist($copy);

        $mail->setStatus(Mail::STATUS_SENDING);
        $this->em->flush();

        return $this->redirectToRoute($this->config->getRootRoute() . '_index');
    }
}
