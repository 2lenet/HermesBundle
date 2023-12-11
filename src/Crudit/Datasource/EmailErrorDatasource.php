<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Datasource;

use Lle\CruditBundle\Datasource\AbstractDoctrineDatasource;
use Lle\HermesBundle\Crudit\Datasource\Filterset\EmailErrorFilterSet;
use Lle\HermesBundle\Entity\EmailError;
use Symfony\Contracts\Service\Attribute\Required;

class EmailErrorDatasource extends AbstractDoctrineDatasource
{
    public function getClassName(): string
    {
        return EmailError::class;
    }

    #[Required]
    public function setFilterset(EmailErrorFilterSet $filterSet): void
    {
        $this->filterset = $filterSet;
    }
}
