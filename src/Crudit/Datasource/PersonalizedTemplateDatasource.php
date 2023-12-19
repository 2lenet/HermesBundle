<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Datasource;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Lle\CruditBundle\Datasource\AbstractDoctrineDatasource;
use Lle\CruditBundle\Datasource\DatasourceParams;
use Lle\CruditBundle\Filter\FilterState;
use Lle\HermesBundle\Crudit\Datasource\Filterset\TemplateFilterSet;
use Lle\HermesBundle\Entity\Template;
use Lle\HermesBundle\Service\MultiTenantManager;
use Symfony\Contracts\Service\Attribute\Required;

class PersonalizedTemplateDatasource extends AbstractDoctrineDatasource
{
    public function __construct(
        EntityManagerInterface $entityManager,
        FilterState $filterState,
        private readonly MultiTenantManager $multiTenantManager,
    ) {
        parent::__construct($entityManager, $filterState);
    }

    public function getClassName(): string
    {
        return Template::class;
    }

    #[Required]
    public function setFilterset(TemplateFilterSet $filterSet): void
    {
        $this->filterset = $filterSet;
    }

    public function buildQueryBuilder(?DatasourceParams $requestParams): QueryBuilder
    {
        $qb = parent::buildQueryBuilder($requestParams);

        $qb->andWhere('root.tenantId IS NOT NULL');
        if ($this->multiTenantManager->isMultiTenantEnabled()) {
            $qb->andWhere('root.tenantId = :id')
                ->setParameter('id', $this->multiTenantManager->getTenantId());
        }

        return $qb;
    }
}
