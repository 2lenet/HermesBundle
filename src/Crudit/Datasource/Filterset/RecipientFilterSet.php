<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Datasource\Filterset;

use Lle\CruditBundle\Datasource\AbstractFilterSet;
use Lle\CruditBundle\Filter\FilterType\DateFilterType;
use Lle\CruditBundle\Filter\FilterType\EntityFilterType;
use Lle\CruditBundle\Filter\FilterType\StringFilterType;
use Lle\HermesBundle\Entity\Mail;

class RecipientFilterSet extends AbstractFilterSet
{
    /**
     * @return array
     */
    public function getFilters(): array
    {
        return [
            EntityFilterType::new('mail', Mail::class),
            StringFilterType::new('toName'),
            StringFilterType::new('toEmail'),
            StringFilterType::new('status'),
            DateFilterType::new('mail:sendingDate'),
            DateFilterType::new('openDate')
        ];
    }
}
