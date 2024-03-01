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
    public function getName(): ?string
    {
        return "HERMES_LINK";
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
        $actions = parent::getItemActions();
        unset($actions[CrudConfigInterface::ACTION_EDIT]);
        unset($actions[CrudConfigInterface::ACTION_DELETE]);

        return $actions;
    }

    public function getShowActions(): array
    {
        $actions = parent::getShowActions();
        unset($actions[CrudConfigInterface::ACTION_EDIT]);
        unset($actions[CrudConfigInterface::ACTION_DELETE]);

        return $actions;
    }

    public function getSublistAction(): array
    {
        $actions = parent::getItemActions();
        unset($actions[CrudConfigInterface::ACTION_EDIT]);
        unset($actions[CrudConfigInterface::ACTION_DELETE]);

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
