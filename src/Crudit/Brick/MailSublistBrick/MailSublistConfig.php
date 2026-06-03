<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Brick\MailSublistBrick;

use Lle\CruditBundle\Brick\SublistBrick\SublistConfig;
use Lle\CruditBundle\Contracts\CrudConfigInterface;
use Symfony\Component\HttpFoundation\Request;

class MailSublistConfig extends SublistConfig
{
    public function __construct(
        string $fieldname = 'mails',
        ?CrudConfigInterface $subCrudConfig = null,
        array $options = []
    ) {
        $this->options = $options;
        $this->setFieldname($fieldname);
        if ($subCrudConfig !== null) {
            $this->subCrudConfig = $subCrudConfig;
        }
    }

    public static function new(
        string $fieldname = 'mails',
        ?CrudConfigInterface $subCrudConfig = null,
        array $options = []
    ): self {
        return new static($fieldname, $subCrudConfig, $options);
    }

    public function setSubCrudConfig(CrudConfigInterface $subCrudConfig): self
    {
        $this->subCrudConfig = $subCrudConfig;

        return $this;
    }

    public function getConfig(Request $request): array
    {
        return array_merge(parent::getConfig($request), [
            'fields' => $this->subCrudConfig->getFields(CrudConfigInterface::INDEX),
            'actions' => $this->subCrudConfig->getItemActions(),
            'translation_domain' => $this->subCrudConfig->getTranslationDomain(),
        ]);
    }
}
