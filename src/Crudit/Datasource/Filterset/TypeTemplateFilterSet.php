<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Datasource\Filterset;

use Lle\CruditBundle\Datasource\AbstractFilterSet;
use Lle\CruditBundle\Filter\FilterType\BooleanFilterType;
use Lle\CruditBundle\Filter\FilterType\StringFilterType;

class TypeTemplateFilterSet extends AbstractFilterSet
{
    public function getFilters(): array
    {
        return [
            StringFilterType::new('label'),
            StringFilterType::new('code'),
            BooleanFilterType::new('unsubscriptionsAllowed'),
        ];
    }
}
