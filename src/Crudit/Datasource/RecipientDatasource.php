<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Datasource;

use Lle\CruditBundle\Datasource\AbstractDoctrineDatasource;
use Lle\HermesBundle\Crudit\Datasource\Filterset\RecipientFilterSet;
use Lle\HermesBundle\Entity\Recipient;

class RecipientDatasource extends AbstractDoctrineDatasource
{
    public function getClassName(): string
    {
        return Recipient::class;
    }

    /**
     * @param RecipientFilterSet $filterSet
     */
    public function setFilterset(RecipientFilterSet $filterSet): void
    {
        $this->filterset = $filterSet;
    }
}
