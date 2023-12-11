<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Config;

use Lle\CruditBundle\Brick\SublistBrick\SublistConfig;
use Lle\CruditBundle\Contracts\CrudConfigInterface;
use Lle\CruditBundle\Dto\Field\Field;
use Lle\HermesBundle\Crudit\Datasource\EmailErrorDatasource;

class EmailErrorCrudConfig extends AbstractCrudConfig
{
    public function __construct(
        EmailErrorDatasource $datasource,
        protected readonly ErrorCrudConfig $errorCrudConfig,
    ) {
        $this->datasource = $datasource;
    }

    /**
     * @param string $key
     * @return Field[]
     */
    public function getFields($key): array
    {
        $email = Field::new('email');
        $nbError = Field::new('nbError');

        if ($key == CrudConfigInterface::INDEX || $key == CrudConfigInterface::SHOW) {
            return [
                $email,
                $nbError,
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

    public function getTabs(): array
    {
        return [
            'tab.errors' => SublistConfig::new('emailError', $this->errorCrudConfig)
                ->setFields($this->errorCrudConfig->getFields(CrudConfigInterface::INDEX))
                ->setActions($this->errorCrudConfig->getItemActions()),
        ];
    }

    public function getRootRoute(): string
    {
        return 'lle_hermes_crudit_emailerror';
    }
}
