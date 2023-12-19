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

    public function getItemActions(): array
    {
        $actions = parent::getItemActions();

        unset($actions[TemplateCrudConfig::ACTION_DUPLICATE]);
        unset($actions[TemplateCrudConfig::ACTION_COPY_FOR_TENANT]);

        return $actions;
    }

    public function getRootRoute(): string
    {
        return 'lle_hermes_crudit_personalizedtemplate';
    }

    public function getListActions(): array
    {
        return [];
    }
}
