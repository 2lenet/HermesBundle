<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Controller\Crudit;

use App\Entity\Establishment;
use Doctrine\ORM\EntityManagerInterface;
use Lle\CruditBundle\Brick\BrickResponse\FlashBrickResponse;
use Lle\CruditBundle\Controller\AbstractCrudController;
use Lle\CruditBundle\Controller\TraitCrudController;
use Lle\HermesBundle\Crudit\Config\TemplateCrudConfig;
use Lle\HermesBundle\Entity\Template;
use Lle\HermesBundle\Repository\TemplateRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
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

    public function __construct(TemplateCrudConfig $config, EntityManagerInterface $em, TemplateRepository $templateRepository, TranslatorInterface $translator)
    {
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

    /**
     * @Route("/copy-for-every-tenant/{id}", name="lle_hermes_crudit_template_copyforeverytenant", methods={"GET"})
     */
    public function copyForEveryTenant(Template $template, ParameterBagInterface $parameterBag): Response
    {

        $tenantClass = $parameterBag->get('lle_hermes.tenant_class');
        $entities = $this->em->getRepository($tenantClass)->findBy([]);
        $count = 0;
        if (array_key_exists(
                strtolower(@end(explode('\\', $tenantClass))) . '_filter',
                $this->em->getFilters()->getEnabledFilters()
            )) {

        }
        foreach ($this->em->getFilters()->getEnabledFilters() as $filter) {
            dd($filter->hasParameter());
        }
        dd(count($entities));
        foreach ($entities as $entity) {
            $newTemplate = (new Template())
                ->setTenantId($entity->getId())
                ->setCode($template->getCode())
                ->setHtml($template->getHtml())
                ->setMjml($template->getMjml())
                ->setSubject($template->getSubject())
                ->setText($template->getText())
                ->setUnsubscriptions($template->isUnsubscriptions())
                ->setStatistics($template->hasStatistics())
                ->setSenderName($template->getSenderName())
                ->setSenderEmail($template->getSenderEmail())
                ->setLibelle($template->getLibelle());

            $this->em->persist($newTemplate);
            $count++;
        }
        $this->em->flush();
        $message = $this->translator->trans('flash.copy_for_tenants', ['%count%' => $count], 'LleHermesBundle');
        $this->addFlash('success', $message);

        return $this->redirectToRoute('lle_hermes_crudit_template_index');
    }
}
