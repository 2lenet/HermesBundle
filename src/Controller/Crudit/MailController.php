<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Controller\Crudit;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CruditBundle\Brick\BrickResponse\FlashBrickResponse;
use Lle\CruditBundle\Controller\AbstractCrudController;
use Lle\CruditBundle\Controller\TraitCrudController;
use Lle\HermesBundle\Crudit\Config\MailCrudConfig;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Entity\Recipient;
use Lle\HermesBundle\Repository\MailRepository;
use Lle\HermesBundle\Service\AttachmentService;
use Lle\HermesBundle\Service\MultiTenantManager;
use Lle\HermesBundle\Service\Sender;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/mail')]
class MailController extends AbstractCrudController
{
    use TraitCrudController {
        TraitCrudController::show as traitShow;
        TraitCrudController::delete as traitDelete;
    }

    public function __construct(
        MailCrudConfig $config,
        protected AttachmentService $attachmentService,
        protected readonly EntityManagerInterface $em,
        protected readonly MailRepository $mailRepository,
        protected readonly TranslatorInterface $translator,
        protected readonly Sender $sender,
        protected readonly MultiTenantManager $multiTenantManager,
    ) {
        $this->config = $config;
    }

    #[Route('/dashboard', name: 'lle_hermes_dashboard', methods: ['GET'])]
    public function dashboard(Request $request): Response
    {
        $this->denyAccessUnlessGranted("ROLE_HERMES_DASHBOARD");

        $number = (int)$request->get("number", 30);
        $page = (int)$request->get("page", 1);

        $tenantId = null;
        if ($this->multiTenantManager->isMultiTenantEnabled()) {
            $tenantId = $this->multiTenantManager->getTenantId();
        }

        $mails = $this->mailRepository->getDashboardMails($page, $number, $tenantId);

        $total = count($mails);
        $from = $number * ($page - 1) + 1;
        $to = min($number * $page, $total);
        $totalPages = intdiv($total, $number) + ($total % $number > 0 ? 1 : 0);

        return $this->render("@LleHermes/Dashboard/dashboard.html.twig", [
            "mails" => $mails,
            "total" => $total,
            "from" => $from,
            "to" => $to,
            "page" => $page,
            "total_pages" => $totalPages,
        ]);
    }

    #[Route('/send/{id}', name: 'lle_hermes_crudit_mail_send', methods: ['GET'])]
    public function send(Mail $mail): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_HERMES_MAIL_SEND');

        if (!$this->multiTenantManager->isOwner($mail)) {
            $this->addFlash(FlashBrickResponse::ERROR, 'flash.not_owner.mail');

            return $this->redirectToRoute($this->config->getRootRoute() . '_index');
        }
        $mail->setStatus(Mail::STATUS_SENDING);
        $this->em->flush();
        return $this->redirectToRoute($this->config->getRootRoute() . '_index');
    }

    #[Route('/show/{id}/{file}', name: 'lle_hermes_crudit_mail_show_attachement', methods: ['GET'])]
    public function showAttachement(Mail $mail, string $file): ?BinaryFileResponse
    {
        $this->denyAccessUnlessGranted('ROLE_HERMES_MAIL_SHOW');

        $path = $mail->getPathOfAttachement($file);
        if (!$path) {
            return null;
        }

        return new BinaryFileResponse($path);
    }

    #[Route('/show/{id}')]
    public function show(Request $request, Mail $mail): Response
    {
        $this->denyAccessUnlessGranted('ROLE_HERMES_MAIL_SHOW');

        if (!$this->multiTenantManager->isOwner($mail)) {
            $this->addFlash(FlashBrickResponse::ERROR, 'flash.not_owner.mail');

            return $this->redirectToRoute($this->config->getRootRoute() . '_index');
        }

        return $this->traitShow($request, $mail);
    }

    #[Route('/delete/{id}')]
    public function delete(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_HERMES_MAIL_DELETE');

        /** @var Mail $mail */
        $mail = $this->getResource($request, false);

        if (!$this->multiTenantManager->isOwner($mail)) {
            $this->addFlash(FlashBrickResponse::ERROR, 'flash.not_owner.mail');

            return $this->redirectToRoute($this->config->getRootRoute() . '_index');
        }

        $this->attachmentService->deleteAttachements($mail);

        return $this->traitDelete($request);
    }

    #[Route('/send_testmail/{id}', name: 'lle_hermes_crudit_mail_send_testmail', methods: ['GET'])]
    public function sendTestMail(Mail $mail, Request $request): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_HERMES_MAIL_SENDTESTMAIL');

        if (!$this->multiTenantManager->isOwner($mail)) {
            $this->addFlash(FlashBrickResponse::ERROR, 'flash.not_owner.mail');

            return $this->redirectToRoute($this->config->getRootRoute() . '_index');
        }

        $email = $request->query->get('email');
        if (!$email) {
            $this->addFlash(FlashBrickResponse::ERROR, 'flash.no_email');

            return $this->redirectToRoute($this->config->getRootRoute() . '_index');
        }

        $recipient = new Recipient();
        $recipient
            ->setMail($mail)
            ->setToEmail($email)
            ->setToName('TEST')
            ->setData(['toEmail' => $email, 'toName' => 'TEST'])
            ->setStatus(Recipient::STATUS_SENDING)
            ->setTest(true);

        $this->em->persist($recipient);
        $this->em->flush();

        $nb = $this->sender->sendRecipient($recipient);

        $message = $this->translator->trans('flash.mail_sent', ['%nb%' => $nb, '%nbTotal%' => 1], 'LleHermesBundle');
        $this->addFlash(FlashBrickResponse::SUCCESS, $message);

        return $this->redirectToRoute($this->config->getRootRoute() . '_index');
    }
}
