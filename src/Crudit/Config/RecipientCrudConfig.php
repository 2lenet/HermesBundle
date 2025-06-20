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
    public function __construct(
        RecipientDatasource $datasource,
    ) {
        $this->datasource = $datasource;
    }

    public function getName(): string
    {
        return 'HERMES_RECIPIENT';
    }

    /**
     * @param string $key
     * @return Field[]
     */
    public function getFields($key): array
    {
        $mail = Field::new('mail', DoctrineEntityField::class, [
            'route' => 'lle_hermes_crudit_mail_show',
            'routeRole' => 'ROLE_HERMES_MAIL_SHOW',
        ]);
        $sendingDate = Field::new('toName');
        $toEmail = Field::new('toEmail');
        $status = Field::new('status')->setTemplate('@LleHermes/crud/_status.html.twig');
        $openDate = Field::new('openDate');
        $linkOpening = Field::new('totalLinkOpening')->setLabel('field.nbopenings')->setSortable(false);
        $data = Field::new('data', 'string')->setTemplate('@LleHermes/crud/recipient/_data.html.twig');
        $errorMessage = Field::new('errorMessage');

        if ($key == CrudConfigInterface::INDEX) {
            return [
                $mail,
                $sendingDate,
                $toEmail,
                $status,
                $openDate,
                $linkOpening,
            ];
        } elseif ($key == CrudConfigInterface::SHOW) {
            return [
                $mail,
                $sendingDate,
                $toEmail,
                $status,
                $openDate,
                $linkOpening,
                $errorMessage,
                $data,
            ];
        }

        return [];
    }

    public function getListActions(): array
    {
        $actions = parent::getListActions();
        unset($actions[CrudConfigInterface::ACTION_ADD]);

        return $actions;
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
        $toName = Field::new('toName');
        $toEmail = Field::new('toEmail');
        $status = Field::new('status')->setTemplate('@LleHermes/crud/_status.html.twig');
        $openDate = Field::new('openDate');
        $linkOpening = Field::new('totalLinkOpening')
            ->setLabel('field.nbopeningsLinks')
            ->setSortable(false);

        return [
            $toName,
            $toEmail,
            $status,
            $openDate,
            $linkOpening,
        ];
    }

    public function getDefaultSort(): array
    {
        return [
            ['id', 'ASC'],
        ];
    }

    public function getRootRoute(): string
    {
        return 'lle_hermes_crudit_recipient';
    }
}
