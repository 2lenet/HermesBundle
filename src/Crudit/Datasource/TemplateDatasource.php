<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Datasource;

use Lle\CruditBundle\Datasource\AbstractDoctrineDatasource;
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
}
