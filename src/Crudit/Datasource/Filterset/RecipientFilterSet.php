<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Datasource\Filterset;

use Lle\CruditBundle\Datasource\AbstractFilterSet;
use Lle\CruditBundle\Filter\FilterType\ChoiceFilterType;
use Lle\CruditBundle\Filter\FilterType\EntityFilterType;
use Lle\CruditBundle\Filter\FilterType\PeriodeFilterType;
use Lle\CruditBundle\Filter\FilterType\StringFilterType;
use Lle\HermesBundle\Entity\Recipient;

class RecipientFilterSet extends AbstractFilterSet
{
    public function getFilters(): array
    {
        return [
            StringFilterType::new('toName'),
            StringFilterType::new('toEmail'),
            ChoiceFilterType::new('status', [
                null,
                Recipient::STATUS_SENDING,
                Recipient::STATUS_SENT,
                Recipient::STATUS_CANCELLED,
                Recipient::STATUS_UNSUBSCRIBED,
                Recipient::STATUS_ERROR,
            ]),
            PeriodeFilterType::new('mail:sendingDate'),
            PeriodeFilterType::new('openDate'),
        ];
    }
}
