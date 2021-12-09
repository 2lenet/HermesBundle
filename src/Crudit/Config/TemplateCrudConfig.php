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
    )
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
        $mjml = Field::new('mjml');
        $text = Field::new('text');
        $code = Field::new('code');
        $html = Field::new('html');
        $unsubscriptions = Field::new('unsubscriptions');
        // you can return different fields based on the block key
        if ($key == CrudConfigInterface::INDEX || $key == CrudConfigInterface::SHOW) {
            return [
                $libelle,
                $subject,
                $senderName,
                $senderEmail,
                $mjml,
                $text,
                $code,
                $html,
                $unsubscriptions,
            ];
        }

        return [];
    }

    public function getRootRoute(): string
    {
        return 'lle_hermes_crudit_template';
    }
}
