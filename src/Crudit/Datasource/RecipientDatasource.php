<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Datasource;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Lle\CruditBundle\Datasource\AbstractDoctrineDatasource;
use Lle\CruditBundle\Datasource\DatasourceParams;
use Lle\CruditBundle\Filter\FilterState;
use Lle\HermesBundle\Crudit\Datasource\Filterset\RecipientFilterSet;
use Lle\HermesBundle\Entity\Recipient;
use Lle\HermesBundle\Service\MultiTenantManager;
use Symfony\Contracts\Service\Attribute\Required;

class RecipientDatasource extends AbstractDoctrineDatasource
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
        return Recipient::class;
    }

    #[Required]
    public function setFilterset(RecipientFilterSet $filterSet): void
    {
        $this->filterset = $filterSet;
    }

    public function buildQueryBuilder(?DatasourceParams $requestParams): QueryBuilder
    {
        $qb = parent::buildQueryBuilder($requestParams);
        $qb->andWhere('root.test = false');

        if ($this->multiTenantManager->isMultiTenantEnabled()) {
            $qb->andWhere('root.tenantId = :id')
                ->setParameter('id', $this->multiTenantManager->getTenantId());
        }

        return $qb;
    }
}
