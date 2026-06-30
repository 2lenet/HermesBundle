<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Controller\Crudit;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CruditBundle\Brick\BrickResponse\FlashBrickResponse;
use Lle\CruditBundle\Controller\AbstractCrudController;
use Lle\CruditBundle\Controller\TraitCrudController;
use Lle\HermesBundle\Contracts\TemplateInterface;
use Lle\HermesBundle\Crudit\Config\TemplateCrudConfig;
use Lle\HermesBundle\Form\TemplateType;
use Lle\HermesBundle\Contracts\TemplateRepositoryInterface;
use Lle\HermesBundle\Service\MultiTenantManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
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
        protected readonly TemplateRepositoryInterface $templateRepository,
        protected readonly TranslatorInterface $translator,
        protected readonly MultiTenantManager $multiTenantManager,
        #[Autowire(param: 'lle_hermes.template_class')]
        protected readonly string $templateClass,
    ) {
        $this->config = $config;
    }

    #[Route('/new')]
    public function new(Request $request): Response
    {
        $type = $request->query->getString('type');

        /** @var TemplateInterface $template */
        $template = new ($this->templateClass)();
        $template->setType($type);

        $form = $this->createForm(TemplateType::class, $template)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($template);
            $this->em->flush();

            return $this->redirectToRoute('lle_hermes_crudit_template_show', ['id' => $template->getId()]);
        }

        return $this->render('@LleHermes/crud/template/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/duplicate/{id}', name: 'lle_hermes_template_duplicate', methods: ['GET'])]
    public function duplicate(TemplateInterface $template): Response
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


    #[Route('/copy-for-tenant/{id}', name: 'lle_hermes_crudit_template_copyfortenant', methods: ['GET'])]
    public function copyForTenant(TemplateInterface $template, Request $request): Response
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

        $newTemplate = $this->templateRepository->duplicateTemplate($template, (string)$template->getCode())
            ->setTenantId($entity->getId())
            ->setLibelle((string)$template->getLibelle());

        $this->em->persist($newTemplate);
        $this->em->flush();
        $this->addFlash(FlashBrickResponse::SUCCESS, 'flash.copy_for_tenants');

        return $this->redirectToRoute('lle_hermes_crudit_personalizedtemplate_index');
    }
}
