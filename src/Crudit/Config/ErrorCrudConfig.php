<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Config;

use Lle\CruditBundle\Contracts\CrudConfigInterface;
use Lle\CruditBundle\Dto\Field\Field;
use Lle\HermesBundle\Crudit\Datasource\ErrorDatasource;

class ErrorCrudConfig extends AbstractCrudConfig
{
    public function __construct(ErrorDatasource $datasource)
    {
        $this->datasource = $datasource;
    }

    public function getName(): string
    {
        return 'HERMES_ERROR';
    }

    /**
     * @param string $key
     * @return Field[]
     */
    public function getFields($key): array
    {
        $date = Field::new('date');
        $subject = Field::new('subject');
        $content = Field::new('content')->setCssClass('col-12');

        if ($key == CrudConfigInterface::INDEX) {
            return [
                $date,
                $subject,
            ];
        }

        if ($key == CrudConfigInterface::SHOW) {
            return [
                $date,
                $subject,
                $content,
            ];
        }

        return [];
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
        return 'lle_hermes_crudit_error';
    }
}
