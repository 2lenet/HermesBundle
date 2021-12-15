<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Config;

use Lle\CruditBundle\Contracts\CrudConfigInterface;
use Lle\CruditBundle\Dto\Field\Field;
use Lle\HermesBundle\Crudit\Datasource\MailDatasource;

class MailCrudConfig extends AbstractCrudConfig
{
    public function __construct(
        MailDatasource $datasource
    ) {
        $this->datasource = $datasource;
    }

    /**
     * @param string $key
     * @return Field[]
     */
    public function getFields($key): array
    {
        $subject = Field::new('subject');
        $sendingDate = Field::new('sendingDate');
        $status = Field::new('status');
        // you can return different fields based on the block key
        if ($key == CrudConfigInterface::INDEX || $key == CrudConfigInterface::SHOW) {
            return [
                $subject,
                $sendingDate,
                $status
            ];
        }

        return [];
    }

    public function getRootRoute(): string
    {
        return 'lle_hermes_crudit_mail';
    }
}
