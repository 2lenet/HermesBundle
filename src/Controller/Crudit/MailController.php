<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Controller\Crudit;

use Lle\CruditBundle\Controller\AbstractCrudController;
use Lle\CruditBundle\Controller\TraitCrudController;
use Lle\HermesBundle\Crudit\Config\MailCrudConfig;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Repository\MailRepository;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/mail")
 */
class MailController extends AbstractCrudController
{
    use TraitCrudController;

    private MailRepository $mailRepository;

    public function __construct(MailCrudConfig $config, MailRepository $mailRepository)
    {
        $this->config = $config;
        $this->mailRepository = $mailRepository;
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
     * @Route("/show/{id}/{file}", name="lle_hermes_crudit_mail_show_attachement", methods={"GET"})
     */
    public function showAttachement(Mail $mail, string $file)
    {
        return new BinaryFileResponse($mail->getPathOfAttachement($file));
    }
}
