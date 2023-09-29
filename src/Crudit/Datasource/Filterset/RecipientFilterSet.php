<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Datasource\Filterset;

use Lle\CruditBundle\Datasource\AbstractFilterSet;
use Lle\CruditBundle\Filter\FilterType\DateFilterType;
use Lle\CruditBundle\Filter\FilterType\EntityFilterType;
use Lle\CruditBundle\Filter\FilterType\StringFilterType;

class RecipientFilterSet extends AbstractFilterSet
{
    public function getFilters(): array
    {
        return [
            StringFilterType::new('toName'),
            StringFilterType::new('toEmail'),
            StringFilterType::new('status'),
            DateFilterType::new('mail:sendingDate'),
            DateFilterType::new('openDate'),
        ];
    }
}
