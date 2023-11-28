<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Controller\Crudit;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CruditBundle\Brick\BrickResponse\FlashBrickResponse;
use Lle\CruditBundle\Controller\AbstractCrudController;
use Lle\CruditBundle\Controller\TraitCrudController;
use Lle\HermesBundle\Crudit\Config\TemplateCrudConfig;
use Lle\HermesBundle\Entity\Template;
use Lle\HermesBundle\Contracts\MultiTenantInterface;
use Lle\HermesBundle\Repository\TemplateRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
    ) {
        $this->config = $config;
    }

    #[Route('/duplicate/{id}', name: 'lle_hermes_template_duplicate', methods: ['GET'])]
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


    #[Route('/copy-for-tenant/{id}', name:'lle_hermes_crudit_template_copyfortenant', methods:['GET'])]
    public function copyForTenant(Template $template, Request $request, ParameterBagInterface $parameterBag): Response
    {
        /** @var class-string $tenantClass */
        $tenantClass = $parameterBag->get('lle_hermes.tenant_class');
        /** @var MultiTenantInterface $user */
        $user = $this->getUser();
        $entity = $this->em->getRepository($tenantClass)->findOneBy(['id' => $user->getTenantId()]);
        if (!$entity || !method_exists($entity, 'getId')) {
            $this->addFlash('danger', 'flash.no_entity_found');
            return $this->redirectToRoute('lle_hermes_crudit_template_index');
        }

        $newTemplate = (new Template())
            ->setTenantId($entity->getId())
            ->setCode($template->getCode())
            ->setHtml($template->getHtml())
            ->setSubject($template->getSubject())
            ->setText($template->getText())
            ->setUnsubscriptions($template->isUnsubscriptions())
            ->setStatistics($template->hasStatistics())
            ->setSenderName($template->getSenderName())
            ->setSenderEmail($template->getSenderEmail())
            ->setLibelle($template->getLibelle());

        $this->em->persist($newTemplate);
        $this->em->flush();
        $message = $this->translator->trans('flash.copy_for_tenants');
        $this->addFlash('success', $message);

        return $this->redirectToRoute('lle_hermes_crudit_personalizedtemplate_index');
    }
}
