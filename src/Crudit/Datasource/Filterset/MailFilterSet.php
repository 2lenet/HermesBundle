<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Datasource\Filterset;

use Lle\CruditBundle\Datasource\AbstractFilterSet;
use Lle\CruditBundle\Filter\FilterType\ChoiceFilterType;
use Lle\CruditBundle\Filter\FilterType\StringFilterType;
use Lle\HermesBundle\Entity\Mail;

class MailFilterSet extends AbstractFilterSet
{
    public function getFilters(): array
    {
        return [
            StringFilterType::new('subject'),
            StringFilterType::new('sendingDate'),
            ChoiceFilterType::new(
                'status',
                [null, Mail::STATUS_DRAFT, Mail::STATUS_SENDING, Mail::STATUS_SENT, Mail::STATUS_CANCELLED]
            ),
        ];
    }
}
