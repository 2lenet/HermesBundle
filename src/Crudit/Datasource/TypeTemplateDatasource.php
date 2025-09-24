<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Datasource;

use Lle\CruditBundle\Datasource\AbstractDoctrineDatasource;
use Lle\HermesBundle\Crudit\Datasource\Filterset\TypeTemplateFilterSet;
use Lle\HermesBundle\Entity\TypeTemplate;
use Symfony\Contracts\Service\Attribute\Required;

class TypeTemplateDatasource extends AbstractDoctrineDatasource
{
    public function getClassName(): string
    {
        return TypeTemplate::class;
    }

    #[Required]
    public function setFilterset(TypeTemplateFilterSet $filterSet): void
    {
        $this->filterset = $filterSet;
    }
}
