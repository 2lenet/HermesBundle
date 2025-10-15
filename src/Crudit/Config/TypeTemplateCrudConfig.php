<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Config;

use App\Crudit\Datasource\TypeTacheDatasource;
use Lle\CruditBundle\Brick\SublistBrick\SublistConfig;
use Lle\CruditBundle\Contracts\CrudConfigInterface;
use Lle\CruditBundle\Dto\Field\Field;
use Lle\HermesBundle\Crudit\Datasource\TypeTemplateDatasource;

class TypeTemplateCrudConfig extends AbstractCrudConfig
{
    public function __construct(
        TypeTemplateDatasource $datasource,
        protected TemplateCrudConfig $templateCrudConfig,
    ) {
        $this->datasource = $datasource;
    }

    public function getName(): string
    {
        return 'HERMES_TYPETEMPLATE';
    }

    /**
     * @param string $key
     * @return Field[]
     */
    public function getFields($key): array
    {
        $label = Field::new('label');
        $code = Field::new('code');
        $unsubscriptionsAllowed = Field::new('unsubscriptionsAllowed');

        return [
            $label,
            $code,
            $unsubscriptionsAllowed,
        ];
    }

    public function getTabs(): array
    {
        return [
            'tab.templates' => SublistConfig::new('typeTemplate', $this->templateCrudConfig)
                ->setFields($this->templateCrudConfig->getFields(CrudConfigInterface::INDEX))
                ->setActions($this->templateCrudConfig->getItemActions()),
        ];
    }

    public function getRootRoute(): string
    {
        return 'lle_hermes_crudit_typetemplate';
    }
}
