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
    ): void {
        if ($this->translatableMail) {
            $builder->add($name, GedmoTranslatableType::class, array_merge($options, [
                'fields_class' => $fieldClass,
            ]));

            return;
        }

        $builder->add($name, $fieldClass, $options);
    }
}
