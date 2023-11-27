<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Datasource;

use Doctrine\ORM\QueryBuilder;
use Lle\CruditBundle\Datasource\AbstractDoctrineDatasource;
use Lle\CruditBundle\Datasource\DatasourceParams;
use Lle\HermesBundle\Crudit\Datasource\Filterset\TemplateFilterSet;
use Lle\HermesBundle\Entity\Template;
use Symfony\Contracts\Service\Attribute\Required;

class TemplateDatasource extends AbstractDoctrineDatasource
{
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
        $qb=  parent::buildQueryBuilder($requestParams);
        $qb->andWhere('root.tenantId IS NULL');

        return $qb;
    }
}
