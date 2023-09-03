<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Controller\Crudit;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CruditBundle\Controller\AbstractCrudController;
use Lle\CruditBundle\Controller\TraitCrudController;
use Lle\HermesBundle\Crudit\Config\TemplateCrudConfig;
use Lle\HermesBundle\Entity\Template;
use Lle\HermesBundle\Repository\TemplateRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/template")
 */
class TemplateController extends AbstractCrudController
{
    use TraitCrudController;

    private EntityManagerInterface $em;
    private TemplateRepository $templateRepository;
    private TranslatorInterface $translator;

    public function __construct(
        TemplateCrudConfig $config,
        EntityManagerInterface $em,
        TemplateRepository $templateRepository,
        TranslatorInterface $translator
    ) {
        $this->config = $config;
        $this->em = $em;
        $this->templateRepository = $templateRepository;
        $this->translator = $translator;
    }

    /**
     * @Route("/duplicate/{id}", name="lle_hermes_template_duplicate", methods={"GET"})
     */
    public function duplicate(Template $template): Response
    {
        $this->denyAccessUnlessGranted('ROLE_LLE_HERMES');

        $code = $template->getCode() . '_COPY';

        if (!$this->templateRepository->findOneBy(['code' => $code])) {
            $copyTemplate = $this->templateRepository->duplicateTemplate($template, $code);
            $this->em->persist($copyTemplate);
            $this->em->flush();
        } else {
            $this->addFlash('danger', $this->translator->trans('flash.copyAlreadyExist', [], 'LleHermesBundle'));
        }

        return $this->redirectToRoute('lle_hermes_crudit_template_index');
    }
}
