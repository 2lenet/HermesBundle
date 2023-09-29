<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Config;

use Lle\CruditBundle\Contracts\CrudConfigInterface;
use Lle\CruditBundle\Dto\Action\ItemAction;
use Lle\CruditBundle\Dto\Field\Field;
use Lle\CruditBundle\Dto\Icon;
use Lle\CruditBundle\Dto\Path;
use Lle\HermesBundle\Crudit\Datasource\TemplateDatasource;

class TemplateCrudConfig extends AbstractCrudConfig
{
    public function __construct(TemplateDatasource $datasource)
    {
        $this->datasource = $datasource;
    }

    /**
     * @param string $key
     * @return Field[]
     */
    public function getFields($key): array
    {
        $libelle = Field::new('libelle');
        $subject = Field::new('subject');
        $senderName = Field::new('senderName');
        $senderEmail = Field::new('senderEmail');
        $text = Field::new('text');
        $code = Field::new('code');
        $html = Field::new('html')
            ->setTemplate('@LleHermes/crud/_template_html.html.twig')
            ->setCssClass('col-12');
        $unsubscriptions = Field::new('unsubscriptions');
        $statistics = Field::new('statistics');

        if ($key == CrudConfigInterface::INDEX) {
            return [
                $libelle,
                $subject,
                $senderName,
                $senderEmail,
                $code,
                $unsubscriptions,
                $statistics,
            ];
        }

        return [
            $libelle,
            $subject,
            $senderName,
            $senderEmail,
            $text,
            $code,
            $html,
            $unsubscriptions,
            $statistics,
        ];
    }

    public function getItemActions(): array
    {
        $actions = parent::getItemActions();

        $actions[] = ItemAction::new(
            'crud.action.duplicate',
            new Path('lle_hermes_template_duplicate'),
            Icon::new('clone')
        )->setDropdown(true);

        return $actions;
    }

    public function getRootRoute(): string
    {
        return 'lle_hermes_crudit_template';
    }
}
