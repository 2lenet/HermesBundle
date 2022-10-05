<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Datasource;

use Lle\CruditBundle\Datasource\AbstractDoctrineDatasource;
use Lle\HermesBundle\Entity\Link;

class LinkDatasource extends AbstractDoctrineDatasource
{
    public function getClassName(): string
    {
        return Link::class;
    }
}
