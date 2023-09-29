<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Config;

use Lle\CruditBundle\Brick\SublistBrick\SublistConfig;
use Lle\CruditBundle\Contracts\CrudConfigInterface;
use Lle\CruditBundle\Dto\Action\ItemAction;
use Lle\CruditBundle\Dto\Field\Field;
use Lle\CruditBundle\Dto\Icon;
use Lle\CruditBundle\Field\DoctrineEntityField;
use Lle\HermesBundle\Crudit\Datasource\LinkDatasource;

class LinkCrudConfig extends AbstractCrudConfig
{
    public function __construct(
        LinkDatasource $datasource,
        protected readonly LinkOpeningCrudConfig $linkOpeningCrudConfig,
    ) {
        $this->datasource = $datasource;
    }

    /**
     * @param string $key
     * @return Field[]
     */
    public function getFields($key): array
    {
        $mail = Field::new('mail', null, [
            'route' => 'lle_hermes_crudit_mail_show',
        ])->setType(DoctrineEntityField::class);
        $totalOpened = Field::new('totalOpened')->setLabel('field.nbopenings');
        $url = Field::new('url')->setCssClass('col-12');

        if ($key == CrudConfigInterface::INDEX || $key == CrudConfigInterface::SHOW) {
            return [
                $mail,
                $totalOpened,
                $url,
            ];
        }

        return [];
    }

    public function getItemActions(): array
    {
        $actions = [];

        $actions[] = ItemAction::new(
            'action.show',
            $this->getPath(CrudConfigInterface::SHOW),
            Icon::new('search')
        )->setCssClass('btn btn-primary btn-sm mr-1');

        return $actions;
    }

    public function getShowActions(): array
    {
        $actions = [];

        $actions[] = ItemAction::new(
            'action.list',
            $this->getPath(CrudConfigInterface::INDEX),
            Icon::new('list')
        )->setCssClass('btn btn-secondary btn-sm mr-1');

        return $actions;
    }

    public function getSublistAction(): array
    {
        $actions = [];

        $actions[] = ItemAction::new(
            'action.show',
            $this->getPath(CrudConfigInterface::SHOW),
            Icon::new('search')
        )->setCssClass('btn btn-primary btn-sm mr-1');

        return $actions;
    }

    public function getSublistFields(): array
    {
        $url = Field::new('url');
        $linkOpenings = Field::new('totalOpened')->setLabel('field.nbopenings');

        return [
            $url,
            $linkOpenings,
        ];
    }

    public function getTabs(): array
    {
        return [
            'tab.linkOpening' => SublistConfig::new('link', $this->linkOpeningCrudConfig)
                ->setFields($this->linkOpeningCrudConfig->getSublistFields()),
        ];
    }

    public function getRootRoute(): string
    {
        return 'lle_hermes_crudit_link';
    }
}
