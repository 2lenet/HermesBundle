<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Config;

use Lle\CruditBundle\Contracts\CrudConfigInterface;
use Lle\CruditBundle\Dto\Action\ItemAction;
use Lle\CruditBundle\Dto\Field\Field;
use Lle\CruditBundle\Dto\Icon;
use Lle\CruditBundle\Field\DoctrineEntityField;
use Lle\HermesBundle\Crudit\Datasource\LinkOpeningDatasource;

class LinkOpeningCrudConfig extends AbstractCrudConfig
{
    public function __construct(LinkOpeningDatasource $datasource)
    {
        $this->datasource = $datasource;
    }
    function getName(): ?string
    {
        return "HERMES_LINK_OPENING";
    }
    /**
     * @param string $key
     * @return Field[]
     */
    public function getFields($key): array
    {
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

    public function getSublistFields(): array
    {
        $link = Field::new('link');
        $recipient = Field::new('recipient', null, [
            'route' => 'lle_hermes_crudit_recipient_show',
        ]);
        $nbOpening = Field::new('nbOpenings');
        $createdAt = Field::new('createdAt')->setLabel('field.firstLinkOpening');
        $updatedAt = Field::new('updatedAt')->setLabel('field.lastLinkOpening');

        return [
            $link,
            $recipient,
            $nbOpening,
            $createdAt,
            $updatedAt,
        ];
    }

    public function getRootRoute(): string
    {
        return 'lle_hermes_crudit_link_opening';
    }
}
