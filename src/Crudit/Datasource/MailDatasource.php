<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Datasource;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Lle\CruditBundle\Datasource\AbstractDoctrineDatasource;
use Lle\CruditBundle\Datasource\DatasourceParams;
use Lle\CruditBundle\Filter\FilterState;
use Lle\HermesBundle\Crudit\Datasource\Filterset\MailFilterSet;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Service\MultiTenantManager;
use Symfony\Contracts\Service\Attribute\Required;

class MailDatasource extends AbstractDoctrineDatasource
{
    public function __construct(
        EntityManagerInterface $entityManager,
        FilterState $filterState,
        private readonly MultiTenantManager $multiTenantManager
    ) {
        parent::__construct($entityManager, $filterState);
    }

    public function getClassName(): string
    {
        return Mail::class;
    }

    #[Required]
    public function setFilterset(MailFilterSet $filterSet): void
    {
        $this->filterset = $filterSet;
    }

    public function buildQueryBuilder(?DatasourceParams $requestParams): QueryBuilder
    {
        $qb = parent::buildQueryBuilder($requestParams);

        if ($this->multiTenantManager->isMultiTenantEnabled()) {
            $qb->andWhere('root.tenantId = :id')
                ->setParameter('id', $this->multiTenantManager->getTenantId());
        }

        return $qb;
    }
}
