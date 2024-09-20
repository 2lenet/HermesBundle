<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Config;

use Lle\CruditBundle\Contracts\CrudConfigInterface;
use Lle\CruditBundle\Dto\Action\EditAction;
use Lle\CruditBundle\Dto\Action\ItemAction;
use Lle\CruditBundle\Dto\Field\Field;
use Lle\CruditBundle\Dto\Icon;
use Lle\CruditBundle\Dto\Path;
use Lle\HermesBundle\Crudit\Datasource\PersonalizedTemplateDatasource;
use Lle\HermesBundle\Crudit\Datasource\TemplateDatasource;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PersonalizedTemplateCrudConfig extends TemplateCrudConfig
{
    public function __construct(
        PersonalizedTemplateDatasource $datasource,
        ParameterBagInterface $parameterBag,
    ) {
        $this->datasource = $datasource;
        $this->parameterBag = $parameterBag;
    }

    public function getName(): string
    {
        return 'HERMES_PERSONALIZED_TEMPLATE';
    }

    public function getListActions(): array
    {
        $actions = parent::getListActions();

        $actions[CrudConfigInterface::ACTION_ADD]->setRole('ROLE_HERMES_PERSONALIZED_TEMPLATE_NEW');
        $actions[CrudConfigInterface::ACTION_EXPORT]->setRole('ROLE_HERMES_PERSONALIZED_TEMPLATE_EXPORT');

        return $actions;
    }

    public function getItemActions(): array
    {
        $actions = parent::getItemActions();

        $actions[CrudConfigInterface::ACTION_SHOW]->setRole('ROLE_HERMES_PERSONALIZED_TEMPLATE_SHOW');
        $actions[CrudConfigInterface::ACTION_EDIT]->setRole('ROLE_HERMES_PERSONALIZED_TEMPLATE_EDIT');
        $actions[CrudConfigInterface::ACTION_DELETE]->setRole('ROLE_HERMES_PERSONALIZED_TEMPLATE_DELETE');

        unset($actions[TemplateCrudConfig::ACTION_DUPLICATE]);
        unset($actions[TemplateCrudConfig::ACTION_COPY_FOR_TENANT]);

        return $actions;
    }

    public function getShowActions(): array
    {
        $actions = parent::getShowActions();

        $actions[CrudConfigInterface::ACTION_LIST]->setRole('ROLE_HERMES_PERSONALIZED_TEMPLATE_INDEX');
        $actions[CrudConfigInterface::ACTION_EDIT]->setRole('ROLE_HERMES_PERSONALIZED_TEMPLATE_EDIT');
        $actions[CrudConfigInterface::ACTION_DELETE]->setRole('ROLE_HERMES_PERSONALIZED_TEMPLATE_DELETE');

        return $actions;
    }

    public function getRootRoute(): string
    {
        return 'lle_hermes_crudit_personalizedtemplate';
    }
}
