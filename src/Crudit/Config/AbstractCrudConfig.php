<?php

namespace Lle\HermesBundle\Crudit\Config;

use Lle\CruditBundle\Crud\AbstractCrudConfig as DefaultConfig;

/**
 * Class AbstractCrudConfig
 * @package App\Crudit\Config
 */
abstract class AbstractCrudConfig extends DefaultConfig
{
    protected function getFormType(string $pageKey): ?string
    {
        return str_replace(
            'Lle\HermesBundle\Crudit\Config',
            'Lle\HermesBundle\Form',
            str_replace('CrudConfig', 'Type', static::class)
        );
    }

    public function getTranslationDomain(): string
    {
        return 'LleHermesBundle';
    }
}
