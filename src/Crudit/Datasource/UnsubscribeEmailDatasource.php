<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Datasource;

use Lle\CruditBundle\Datasource\AbstractDoctrineDatasource;
use Lle\HermesBundle\Crudit\Datasource\Filterset\UnsubscribeEmailFilterSet;
use Lle\HermesBundle\Entity\UnsubscribeEmail;
use Symfony\Contracts\Service\Attribute\Required;

class UnsubscribeEmailDatasource extends AbstractDoctrineDatasource
{
    public function getClassName(): string
    {
        return UnsubscribeEmail::class;
    }

    #[Required]
    public function setFilterset(UnsubscribeEmailFilterSet $filterSet): void
    {
        $this->filterset = $filterSet;
    }
}
