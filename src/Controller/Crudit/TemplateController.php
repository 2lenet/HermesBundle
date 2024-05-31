<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Controller\Crudit;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CruditBundle\Brick\BrickResponse\FlashBrickResponse;
use Lle\CruditBundle\Controller\AbstractCrudController;
use Lle\CruditBundle\Controller\TraitCrudController;
use Lle\HermesBundle\Crudit\Config\TemplateCrudConfig;
use Lle\HermesBundle\Entity\Template;
use Lle\HermesBundle\Repository\TemplateRepository;
use Lle\HermesBundle\Service\MultiTenantManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/template')]
class TemplateController extends AbstractCrudController
{
    use TraitCrudController;

    public function __construct(
        TemplateCrudConfig $config,
        protected readonly EntityManagerInterface $em,
        protected readonly TemplateRepository $templateRepository,
        protected readonly TranslatorInterface $translator,
        protected readonly MultiTenantManager $multiTenantManager,
    ) {
        $this->config = $config;
    }

    #[Route('/duplicate/{id}', name: 'lle_hermes_template_duplicate', methods: ['GET'])]
    public function duplicate(Template $template): Response
    {
        $this->denyAccessUnlessGranted('ROLE_HERMES_TEMPLATE_DUPLICATE');

        $code = $template->getCode() . '_COPY';

        if (!$this->templateRepository->findOneBy(['code' => $code])) {
            $copyTemplate = $this->templateRepository->duplicateTemplate($template, $code);
            $this->em->persist($copyTemplate);
            $this->em->flush();
        } else {
            $this->addFlash(
                FlashBrickResponse::ERROR,
                $this->translator->trans('flash.copyAlreadyExist', [], 'LleHermesBundle')
            );
        }

        return $this->redirectToRoute('lle_hermes_crudit_template_index');
    }


    #[Route('/copy-for-tenant/{id}', name:'lle_hermes_crudit_template_copyfortenant', methods:['GET'])]
    public function copyForTenant(Template $template, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_HERMES_TEMPLATE_COPYFORTENANT');

        /** @var class-string $tenantClass */
        $tenantClass = $this->multiTenantManager->getTenantClass();
        $tenantId = $this->multiTenantManager->getTenantId();
        $entity = $this->em->getRepository($tenantClass)->find($tenantId);
        if (!$entity || !method_exists($entity, 'getId')) {
            $this->addFlash(FlashBrickResponse::ERROR, 'flash.no_entity_found');

            return $this->redirectToRoute('lle_hermes_crudit_template_index');
        }

        $newTemplate = $this->templateRepository->duplicateTemplate($template, $template->getCode())
            ->setTenantId($entity->getId())
            ->setLibelle($template->getLibelle());

        $this->em->persist($newTemplate);
        $this->em->flush();
        $this->addFlash(FlashBrickResponse::SUCCESS, 'flash.copy_for_tenants');

        return $this->redirectToRoute('lle_hermes_crudit_personalizedtemplate_index');
    }
}
