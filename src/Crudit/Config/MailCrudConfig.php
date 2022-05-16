<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Config;

use Lle\CruditBundle\Contracts\CrudConfigInterface;
use Lle\CruditBundle\Dto\Action\DeleteAction;
use Lle\CruditBundle\Dto\Action\ItemAction;
use Lle\CruditBundle\Dto\Action\ListAction;
use Lle\CruditBundle\Dto\Field\Field;
use Lle\CruditBundle\Dto\Icon;
use Lle\CruditBundle\Exporter\Exporter;
use Lle\HermesBundle\Crudit\Datasource\MailDatasource;

class MailCrudConfig extends AbstractCrudConfig
{
    public function __construct(
        MailDatasource $datasource
    ) {
        $this->datasource = $datasource;
    }

    /**
     * @param string $key
     * @return Field[]
     */
    public function getFields($key): array
    {
        $subject = Field::new('subject')
            ->setTemplate('@LleHermes/crud/_subject.html.twig');
        $sendingDate = Field::new('sendingDate');
        $status = Field::new('status');
        $html = Field::new('html')
            ->setTemplate('@LleHermes/crud/_html.html.twig')
            ->setCssClass('col-12');

        $fields = [];

        switch ($key) {
            case CrudConfigInterface::SHOW:
                $fields = [
                    $subject,
                    $sendingDate,
                    $status,
                    $html,
                ];
                break;
            default:
                $fields = [
                    $subject,
                    $sendingDate,
                    $status,
                ];
        }

        return $fields;
    }

    public function getListActions(): array
    {
        $actions = [];

        /**
         * Export filtered list action
         */
        $actions[] = ListAction::new(
            "action.export",
            $this->getPath(CrudConfigInterface::EXPORT),
            Icon::new("file-export")
        )
            ->setModal("@LleCrudit/modal/_export.html.twig")
            ->setConfig(
                [
                    "export" => [Exporter::EXCEL, Exporter::CSV],
                ]
            );

        return $actions;
    }

    public function getItemActions(): array
    {
        $actions = [];
        $actions[] = ItemAction::new(
            'action.show',
            $this->getPath(CrudConfigInterface::SHOW),
            Icon::new('search')
        )->setCssClass('btn btn-primary btn-sm mr-1');
        $actions[] = DeleteAction::new(
            'action.delete',
            $this->getPath(CrudConfigInterface::DELETE),
            Icon::new('trash-alt')
        )
            ->setCssClass('btn btn-danger btn-sm mr-1')
            ->setModal("@LleCrudit/modal/_confirm_delete.html.twig");

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

        $actions[] = DeleteAction::new(
            'action.delete',
            $this->getPath(CrudConfigInterface::DELETE),
            Icon::new('trash-alt')
        )
            ->setCssClass('btn btn-danger btn-sm mr-1')
            ->setModal("@LleCrudit/modal/_confirm_delete.html.twig");

        return $actions;
    }

    public function getRootRoute(): string
    {
        return 'lle_hermes_crudit_mail';
    }
}
