<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Config;

use Lle\CruditBundle\Contracts\CrudConfigInterface;
use Lle\CruditBundle\Dto\Action\ItemAction;
use Lle\CruditBundle\Dto\Field\Field;
use Lle\CruditBundle\Dto\Icon;
use Lle\CruditBundle\Field\DoctrineEntityField;
use Lle\HermesBundle\Crudit\Datasource\RecipientDatasource;

class RecipientCrudConfig extends AbstractCrudConfig
{
    public function __construct(RecipientDatasource $datasource)
    {
        $this->datasource = $datasource;
    }

    /**
     * @param string $key
     * @return Field[]
     */
    public function getFields($key): array
    {
        $mail = Field::new('mail', null, [
            "route" => "lle_hermes_crudit_mail_show",
        ])->setType(DoctrineEntityField::class);
        $sendingDate = Field::new('toName');
        $toEmail = Field::new('toEmail');
        $status = Field::new('status')->setTemplate('@LleHermes/crud/_status.html.twig');
        $openDate = Field::new('openDate');

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
        $sendingDate = Field::new('toName');
        $toEmail = Field::new('toEmail');
        $status = Field::new('status')->setTemplate('@LleHermes/crud/_status.html.twig');
        $openDate = Field::new('openDate');

        return [
            $sendingDate,
            $toEmail,
            $status,
            $openDate
        ];
    }

    public function getRootRoute(): string
    {
        return 'lle_hermes_crudit_recipient';
    }
}
