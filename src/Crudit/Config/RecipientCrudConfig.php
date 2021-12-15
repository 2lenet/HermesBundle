<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Config;

use Lle\CruditBundle\Contracts\CrudConfigInterface;
use Lle\CruditBundle\Dto\Field\Field;
use Lle\HermesBundle\Crudit\Datasource\RecipientDatasource;

class RecipientCrudConfig extends AbstractCrudConfig
{
    public function __construct(
        RecipientDatasource $datasource
    ) {
        $this->datasource = $datasource;
    }

    /**
     * @param string $key
     * @return Field[]
     */
    public function getFields($key): array
    {
        $mail = Field::new('mail');
        $sendingDate = Field::new('toName');
        $toEmail = Field::new('toEmail');
        $status = Field::new('status');
        $openDate = Field::new('openDate');
        // you can return different fields based on the block key
        if ($key == CrudConfigInterface::INDEX || $key == CrudConfigInterface::SHOW) {
            return [
                $mail,
                $sendingDate,
                $toEmail,
                $status,
                $openDate
            ];
        }

        return [];
    }

    public function getRootRoute(): string
    {
        return 'lle_hermes_crudit_recipient';
    }
}
