<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Config;

use Lle\CruditBundle\Contracts\CrudConfigInterface;
use Lle\CruditBundle\Dto\Field\Field;
use Lle\HermesBundle\Crudit\Datasource\UnsubscribeEmailDatasource;

class UnsubscribeEmailCrudConfig extends AbstractCrudConfig
{
    public function __construct(
        UnsubscribeEmailDatasource $datasource,
    ) {
        $this->datasource = $datasource;
    }

    public function getName(): string
    {
        return 'HERMES_UNSUBSCRIBE_EMAIL';
    }

    /**
     * @param string $key
     * @return Field[]
     */
    public function getFields($key): array
    {
        $email = Field::new('email');
        $unsubscribeDate = Field::new('unsubscribeDate');

        switch ($key) {
            case CrudConfigInterface::INDEX:
            case CrudConfigInterface::SHOW:
                $fields = [
                    $email,
                    $unsubscribeDate,
                ];
                break;
            default:
                $fields = [];
        }

        return $fields;
    }

    public function getListActions(): array
    {
        $actions = parent::getListActions();
        unset($actions[CrudConfigInterface::ACTION_ADD]);

        return $actions;
    }

    public function getItemActions(): array
    {
        $actions = parent::getItemActions();
        unset($actions[CrudConfigInterface::ACTION_EDIT]);

        return $actions;
    }

    public function getShowActions(): array
    {
        $actions = parent::getShowActions();
        unset($actions[CrudConfigInterface::ACTION_EDIT]);

        return $actions;
    }

    public function getRootRoute(): string
    {
        return 'lle_hermes_crudit_unsubscribeemail';
    }
}
