<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Config;

use Lle\CruditBundle\Brick\SublistBrick\SublistConfig;
use Lle\CruditBundle\Contracts\CrudConfigInterface;
use Lle\CruditBundle\Dto\Action\ItemAction;
use Lle\CruditBundle\Dto\Field\Field;
use Lle\CruditBundle\Dto\Icon;
use Lle\CruditBundle\Dto\Path;
use Lle\HermesBundle\Crudit\Datasource\MailDatasource;

class MailCrudConfig extends AbstractCrudConfig
{
    private RecipientCrudConfig $recipientCrudConfig;

    public function __construct(MailDatasource $datasource, RecipientCrudConfig $recipientCrudConfig)
    {
        $this->datasource = $datasource;
        $this->recipientCrudConfig = $recipientCrudConfig;
    }

    /**
     * @param string $key
     * @return Field[]
     */
    public function getFields($key): array
    {
        $subject = Field::new('subject')->setTemplate('@LleHermes/crud/_subject.html.twig');
        $recipients = Field::new('recipients')->setTemplate('@LleHermes/crud/_recipient.html.twig');
        $sendingDate = Field::new('sendingDate');
        $status = Field::new('status')->setTemplate('@LleHermes/crud/_status.html.twig');
        $html = Field::new('html')
            ->setTemplate('@LleHermes/crud/_html.html.twig')
            ->setCssClass('col-12');
        $attachement = Field::new('jsonAttachement')->setTemplate('@LleHermes/crud/_attachement.html.twig');

        if ($key == CrudConfigInterface::SHOW) {
            return [
                $subject,
                $recipients,
                $sendingDate,
                $status,
                $html,
                $attachement,
            ];
        }

        return [
            $subject,
            $recipients,
            $sendingDate,
            $status,
        ];
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

    public function getTabs(): array
    {
        return [
            'crud.tab.recipients' => SublistConfig::new('mail', $this->recipientCrudConfig)
                ->setActions($this->recipientCrudConfig->getSublistAction())
                ->setFields($this->recipientCrudConfig->getSublistFields())
        ];
    }

    public function getRootRoute(): string
    {
        return 'lle_hermes_crudit_mail';
    }
}
