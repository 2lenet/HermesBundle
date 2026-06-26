<?php

namespace Lle\HermesBundle\Translatable\Translation;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;
use Lle\HermesBundle\Translatable\TranslatableTemplate;

#[ORM\Entity]
#[ORM\Table(name: 'lle_hermes_template_translation')]
#[ORM\UniqueConstraint(name: 'lle_hermes_template_translation_unique_idx', columns: ['locale', 'object_id', 'field'])]
class TemplateTranslation extends AbstractPersonalTranslation
{
    #[ORM\ManyToOne(targetEntity: TranslatableTemplate::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'object_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $object;

    public function __construct(string $locale, string $field, string $value)
    {
        $this->setLocale($locale);
        $this->setField($field);
        $this->setContent($value);
    }
}
