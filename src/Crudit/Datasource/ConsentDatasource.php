<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Datasource;

use Lle\CruditBundle\Datasource\AbstractDoctrineDatasource;
use Lle\HermesBundle\Crudit\Datasource\Filterset\ConsentFilterSet;
use Lle\HermesBundle\Entity\Consent;
use Symfony\Contracts\Service\Attribute\Required;

class ConsentDatasource extends AbstractDoctrineDatasource
{
    public function getClassName(): string
    {
        return Consent::class;
    }

    #[Required]
    public function setFilterset(ConsentFilterSet $filterSet): void
    {
        $this->filterset = $filterSet;
    }
}
