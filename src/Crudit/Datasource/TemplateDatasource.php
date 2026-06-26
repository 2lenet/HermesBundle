<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Datasource;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Lle\CruditBundle\Datasource\AbstractDoctrineDatasource;
use Lle\CruditBundle\Datasource\DatasourceParams;
use Lle\CruditBundle\Filter\FilterState;
use Lle\HermesBundle\Crudit\Datasource\Filterset\TemplateFilterSet;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Service\Attribute\Required;

class TemplateDatasource extends AbstractDoctrineDatasource
{
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

    public function buildQueryBuilder(?DatasourceParams $requestParams): QueryBuilder
    {
        $qb = parent::buildQueryBuilder($requestParams);
        $qb->andWhere('root.tenantId IS NULL');

        return $qb;
    }
}
