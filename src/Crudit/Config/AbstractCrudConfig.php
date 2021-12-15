<?php

namespace Lle\HermesBundle\Crudit\Config;

use Lle\CruditBundle\Crud\AbstractCrudConfig as DefaultConfig;

/**
 * Class AbstractCrudConfig
 * @package App\Crudit\Config
 *
 * @author Jérôme PERAT <jerome@2le.net>
 */
abstract class AbstractCrudConfig extends DefaultConfig
{
    /**
     * @param string $pageKey
     * @return string|null
     */
    protected function getFormType(string $pageKey): ?string
    {
        return str_replace(
            'Lle\HermesBundle\Crudit\Config',
            'Lle\HermesBundle\Form',
            str_replace('CrudConfig', 'Type', get_class($this))
        );
    }
}
