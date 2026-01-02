<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Datasource\Filterset;

use Lle\CruditBundle\Datasource\AbstractFilterSet;
use Lle\CruditBundle\Filter\FilterType\ChoiceFilterType;
use Lle\CruditBundle\Filter\FilterType\PeriodeFilterType;
use Lle\CruditBundle\Filter\FilterType\StringFilterType;
use Lle\HermesBundle\Entity\Mail;

class MailFilterSet extends AbstractFilterSet
{
    public function getFilters(): array
    {
        return [
            StringFilterType::new('subject'),
            PeriodeFilterType::new('sendingDate'),
            ChoiceFilterType::new('status', [
                'status.draft' => Mail::STATUS_DRAFT,
                'status.sending' => Mail::STATUS_SENDING,
                'status.sent' => Mail::STATUS_SENT,
                'status.cancelled' => Mail::STATUS_CANCELLED,
                'status.error' => Mail::STATUS_ERROR,
            ]),
            StringFilterType::new('recipients:toEmail')
                ->setLabel('field.toemail'),
        ];
    }
}
