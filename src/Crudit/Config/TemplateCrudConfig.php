<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Config;

use Lle\CruditBundle\Contracts\CrudConfigInterface;
use Lle\CruditBundle\Dto\Field\Field;
use Lle\HermesBundle\Crudit\Datasource\TemplateDatasource;

class TemplateCrudConfig extends AbstractCrudConfig
{
    public function __construct(
        TemplateDatasource $datasource
    ) {
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
        $html = Field::new('html')->setTemplate('@LleHermes/_html.html.twig')->setCssClass('col-12');
        $unsubscriptions = Field::new('unsubscriptions');
        // you can return different fields based on the block key
        if ($key == CrudConfigInterface::INDEX) {
            return [
                $libelle,
                $subject,
                $senderName,
                $senderEmail,
                $code,
                $unsubscriptions,
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
        ];
    }

    public function getRootRoute(): string
    {
        return 'lle_hermes_crudit_template';
    }
}
