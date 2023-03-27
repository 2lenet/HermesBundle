<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Controller\Crudit;

use Lle\CruditBundle\Brick\BrickResponse\FlashBrickResponse;
use Lle\CruditBundle\Controller\AbstractCrudController;
use Lle\CruditBundle\Controller\TraitCrudController;
use Lle\HermesBundle\Crudit\Config\MailCrudConfig;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Entity\Recipient;
use Lle\HermesBundle\Repository\MailRepository;
use Lle\HermesBundle\Service\MailFactory;
use Lle\HermesBundle\Service\SenderService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/mail")
 */
class MailController extends AbstractCrudController
{
    use TraitCrudController;

    private MailRepository $mailRepository;
    private ParameterBagInterface $parameterBag;
    private TranslatorInterface $translator;
    private SenderService $senderService;

    public function __construct(
        MailCrudConfig $config,
        MailRepository $mailRepository,
        ParameterBagInterface $parameterBag,
        TranslatorInterface $translator,
        SenderService $senderService
    )
    {
        $this->config = $config;
        $this->mailRepository = $mailRepository;
        $this->parameterBag = $parameterBag;
        $this->translator = $translator;
        $this->senderService = $senderService;
    }

    /**
     * @Route("/dashboard", name="lle_hermes_dashboard", methods={"GET"})
     */
    public function dashboard(Request $request): Response
    {
        $this->denyAccessUnlessGranted("ROLE_LLE_HERMES");

        $number = (int)$request->get("number", 30);
        $page = (int)$request->get("page", 1);

        $mails = $this->mailRepository->getDashboardMails($page, $number);

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

    /**
     * @Route("/send/{id}", name="lle_hermes_crudit_mail_send", methods={"GET"})
     */
    public function send(Mail $mail, SenderService $senderService)
    {
        $this->denyAccessUnlessGranted('ROLE_MAIL_SEND');

        $recipients = $mail->getRecipients();
        $nb = $senderService->sendAllRecipients($recipients->toArray());

        $message = $this->translator->trans('flash.mail_sended', ['%nb%' => $nb, '%nbTotal%' => $mail->getTotalToSend()], 'LleHermesBundle');
        $this->addFlash(FlashBrickResponse::SUCCESS, $message);

        return $this->redirectToRoute($this->config->getRootRoute() . '_index');
    }

    /**
     * @Route("/show/{id}/{file}", name="lle_hermes_crudit_mail_show_attachement", methods={"GET"})
     */
    public function showAttachement(Mail $mail, string $file)
    {
        return new BinaryFileResponse($mail->getPathOfAttachement($file));
    }

    /**
     * @Route("/delete/{id}")
     */
    public function delete(Request $request): Response
    {
        /** @var Mail $mail */
        $mail = $this->getResource($request, false);

        $this->denyAccessUnlessGranted('ROLE_' . $this->config->getName() . '_DELETE', $mail);

        $attachementsPath = sprintf($this->parameterBag->get('lle_hermes.root_dir') . MailFactory::ATTACHMENTS_DIR, $mail->getId());
        $this->deleteAttachements($attachementsPath);

        $dataSource = $this->config->getDatasource();
        $dataSource->delete($dataSource->getIdentifier($mail));

        return $this->redirectToRoute($this->config->getRootRoute() . '_index');
    }

    private function deleteAttachements(string $path)
    {
        if (file_exists($path)) {
            $files = array_diff(scandir($path), ['.', '..']);
            foreach ($files as $file) {
                if (is_dir($path . '/' . $file)) {
                    $this->deleteAttachements($path . '/' . $file);
                } else {
                    unlink($path . '/' . $file);
                }
            }
            rmdir($path);
        }
    }

    /**
     * @Route("/send_testmail/{id}", name="lle_hermes_crudit_mail_send_testmail")
     */
    public function sendTestMail(Mail $mail, Request $request): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_MAIL_SEND_TESTMAIL');

        $email = $request->query->get('email');

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

        $nb = $this->senderService->sendRecipient($recipient);

        $message = $this->translator->trans('flash.mail_sended', ['%nb%' => $nb, '%nbTotal%' => 1], 'LleHermesBundle');
        $this->addFlash(FlashBrickResponse::SUCCESS, $message);

        return $this->redirectToRoute($this->config->getRootRoute() . '_index');
    }
}
