<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Datasource;

use Lle\CruditBundle\Datasource\AbstractDoctrineDatasource;
use Lle\HermesBundle\Crudit\Datasource\Filterset\MailFilterSet;
use Lle\HermesBundle\Entity\Mail;
use Symfony\Contracts\Service\Attribute\Required;

class MailDatasource extends AbstractDoctrineDatasource
{
    public function getClassName(): string
    {
        return Mail::class;
    }

    #[Required]
    public function setFilterset(MailFilterSet $filterSet): void
    {
        $this->filterset = $filterSet;
    }
}
