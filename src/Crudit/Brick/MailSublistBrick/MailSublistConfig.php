<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Brick\MailSublistBrick;

use Lle\CruditBundle\Brick\SublistBrick\SublistConfig;
use Lle\CruditBundle\Contracts\CrudConfigInterface;
use Symfony\Component\HttpFoundation\Request;

class MailSublistConfig extends SublistConfig
{
    protected ?CrudConfigInterface $customSubCrudConfig = null;

    public function __construct(
        string $fieldname = 'mails',
        ?CrudConfigInterface $subCrudConfig = null,
        array $options = []
    ) {
        $this->setFieldname($fieldname);
        $this->customSubCrudConfig = $subCrudConfig;
        $this->setOptions($options);
    }

    public static function new(
        string $fieldname = 'mails',
        ?CrudConfigInterface $subCrudConfig = null,
        array $options = []
    ): self {
        return new self($fieldname, $subCrudConfig, $options);
    }

    public function setSubCrudConfig(CrudConfigInterface $subCrudConfig): self
    {
        $this->subCrudConfig = $subCrudConfig;

        return $this;
    }

    public function getCustomSubCrudConfig(): ?CrudConfigInterface
    {
        return $this->customSubCrudConfig;
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
