<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Form;

use Lle\CruditBundle\Form\Type\GedmoTranslatableType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormTypeInterface;

trait TranslatableFieldsTrait
{
    protected bool $translatableMail = true;

    /**
     * @param class-string<FormTypeInterface> $fieldClass
     */
    protected function addTranslatable(
        FormBuilderInterface $builder,
        string $name,
        string $fieldClass,
        array $options = [],
        bool $required = false,
    ): void {
        if ($this->translatableMail) {
            // `required` is propagated to the default-locale sub-field by GedmoTranslatableType,
            // ensuring the main property is filled when the field is mandatory in DB.
            $builder->add($name, GedmoTranslatableType::class, array_merge($options, [
                'fields_class' => $fieldClass,
                'required' => $required,
            ]));

            return;
        }

        $builder->add($name, $fieldClass, array_merge(['required' => $required], $options));
    }
}
