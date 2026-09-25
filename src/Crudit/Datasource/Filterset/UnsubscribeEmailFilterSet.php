<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Datasource\Filterset;

use Lle\CruditBundle\Datasource\AbstractFilterSet;
use Lle\CruditBundle\Filter\FilterType\DateTimeFilterType;
use Lle\CruditBundle\Filter\FilterType\EntityFilterType;
use Lle\CruditBundle\Filter\FilterType\StringFilterType;
use Lle\HermesBundle\Entity\TypeTemplate;

class UnsubscribeEmailFilterSet extends AbstractFilterSet
{
    public function getFilters(): array
    {
        return [
            StringFilterType::new('email'),
            EntityFilterType::new('typeTemplate', TypeTemplate::class, 'lle_hermes_crudit_typetemplate_autocomplete'),
            DateTimeFilterType::new('unsubscribeDate'),
        ];
    }
}
