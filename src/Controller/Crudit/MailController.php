<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Controller\Crudit;

use Lle\CruditBundle\Controller\AbstractCrudController;
use Lle\CruditBundle\Controller\TraitCrudController;
use Lle\HermesBundle\Crudit\Config\MailCrudConfig;
use Lle\HermesBundle\Repository\MailRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/mail")
 */
class MailController extends AbstractCrudController
{
    use TraitCrudController;

    public function __construct(MailCrudConfig $config, MailRepository $repository)
    {
        $this->config = $config;
        $this->repo = $repository;
    }

    /**
     * @Route("/dashboard", name="lle_hermes_dashboard")
     */
    public function dashboard(Request $request): Response
    {
        $this->denyAccessUnlessGranted("ROLE_LLE_HERMES");

        $mails = $this->repo->findBy([], ["id" => "DESC"]);

        return $this->render("@LleHermes/Dashboard/dashboard.html.twig", [
            "mails" => $mails
        ]);
    }
}
