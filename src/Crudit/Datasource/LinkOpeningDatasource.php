<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Datasource;

use Lle\CruditBundle\Datasource\AbstractDoctrineDatasource;
use Lle\HermesBundle\Entity\LinkOpening;

class LinkOpeningDatasource extends AbstractDoctrineDatasource
{
    public function getClassName(): string
    {
        return LinkOpening::class;
    }
}
