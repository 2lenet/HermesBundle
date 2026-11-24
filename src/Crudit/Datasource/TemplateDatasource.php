<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Datasource;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Lle\CruditBundle\Datasource\AbstractDoctrineDatasource;
use Lle\CruditBundle\Datasource\DatasourceParams;
use Lle\CruditBundle\Filter\FilterState;
use Lle\HermesBundle\Contracts\TemplateInterface;
use Lle\HermesBundle\Crudit\Datasource\Filterset\TemplateFilterSet;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Service\Attribute\Required;

class TemplateDatasource extends AbstractDoctrineDatasource
{
    public const string TYPE_PARAMETER = 'type';

    private const array TYPES = [
        TemplateInterface::TYPE_HTML,
        TemplateInterface::TYPE_CKEDITOR,
        TemplateInterface::TYPE_MJML,
    ];

    private ?RequestStack $requestStack = null;

    public function __construct(
        EntityManagerInterface $entityManager,
        FilterState $filterState,
        #[Autowire(param: 'lle_hermes.template_class')]
        private string $templateClass,
    ) {
        parent::__construct($entityManager, $filterState);
    }

    public function getClassName(): string
    {
        return $this->templateClass;
    }

    #[Required]
    public function setFilterset(TemplateFilterSet $filterSet): void
    {
        $this->filterset = $filterSet;
    }

    #[Required]
    public function setRequestStack(RequestStack $requestStack): void
    {
        $this->requestStack = $requestStack;
    }

    /**
     * The form depends on the type of the template, chosen beforehand (see add_modal.html.twig) and given in the
     * "type" parameter of the request.
     */
    public function newInstance(): object
    {
        $template = parent::newInstance();

        $type = $this->requestStack?->getMainRequest()?->query->getString(self::TYPE_PARAMETER);
        if ($template instanceof TemplateInterface && in_array($type, self::TYPES, true)) {
            $template->setType($type);
        }

        return $template;
    }

    public function buildQueryBuilder(?DatasourceParams $requestParams): QueryBuilder
    {
        $qb = parent::buildQueryBuilder($requestParams);
        $qb->andWhere('root.tenantId IS NULL');

        return $qb;
    }
}
