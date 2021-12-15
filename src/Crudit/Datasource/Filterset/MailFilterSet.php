<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Datasource\Filterset;

use Lle\CruditBundle\Datasource\AbstractFilterSet;
use Lle\CruditBundle\Filter\FilterType\StringFilterType;

class MailFilterSet extends AbstractFilterSet
{
    /**
     * @return array
     */
    public function getFilters(): array
    {
        return [
            StringFilterType::new('subject'),
            StringFilterType::new('sendingDate'),
            StringFilterType::new('status'),
        ];
    }
}
