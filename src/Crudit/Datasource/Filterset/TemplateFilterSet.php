<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Datasource\Filterset;

use Lle\CruditBundle\Datasource\AbstractFilterSet;
use Lle\CruditBundle\Filter\FilterType\StringFilterType;

class TemplateFilterSet extends AbstractFilterSet
{
    /**
     * @return array
     */
    public function getFilters(): array
    {
        return [
            StringFilterType::new('libelle'),
            StringFilterType::new('subject'),
            StringFilterType::new('senderName'),
            StringFilterType::new('senderEmail'),
            StringFilterType::new('code'),
            StringFilterType::new('unsubscriptions'),
        ];
    }
}
