<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Datasource\Filterset;

use Lle\CruditBundle\Datasource\AbstractFilterSet;
use Lle\CruditBundle\Filter\FilterType\ChoiceFilterType;
use Lle\CruditBundle\Filter\FilterType\PeriodeFilterType;
use Lle\CruditBundle\Filter\FilterType\StringFilterType;
use Lle\HermesBundle\Entity\Consent;

class ConsentFilterSet extends AbstractFilterSet
{
    public function getFilters(): array
    {
        return [
            StringFilterType::new('email'),
            ChoiceFilterType::new('type', [
                'consent.type.pixel' => Consent::TYPE_PIXEL,
            ]),
            PeriodeFilterType::new('datetime'),
        ];
    }
}
