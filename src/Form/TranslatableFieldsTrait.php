<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Form;

use Lle\CruditBundle\Form\Type\GedmoTranslatableType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

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
            $builder->add($name, GedmoTranslatableType::class, array_merge($options, [
                'fields_class' => $fieldClass,
                'required' => $required,
            ]));

            if ($required) {
                // GedmoTranslatableType persists the value into the translations table and
                // relies on its read-time fallback, so the entity's main property can stay null
                // even when the default locale was filled. We run after the widget POST_SUBMIT
                // (priority -100) and, if the main property is still empty, copy the first
                // non-empty translation into it so the NOT NULL column is satisfied at flush.
                // Gedmo keeps serving the proper translated value at read time.
                $builder->get($name)->addEventListener(
                    FormEvents::POST_SUBMIT,
                    static function (FormEvent $event): void {
                        $form = $event->getForm();
                        $entity = $form->getParent()?->getData();
                        if ($entity === null || !method_exists($entity, 'getTranslations')) {
                            return;
                        }

                        $fieldName = $form->getName();
                        $accessor = PropertyAccess::createPropertyAccessor();
                        $value = $accessor->getValue($entity, $fieldName);
                        if ($value !== null && (!is_string($value) || trim($value) !== '')) {
                            return;
                        }

                        foreach ($entity->getTranslations() as $translation) {
                            if ($translation->getField() !== $fieldName) {
                                continue;
                            }
                            $content = $translation->getContent();
                            if ($content !== null && trim((string) $content) !== '') {
                                $accessor->setValue($entity, $fieldName, $content);

                                return;
                            }
                        }
                    },
                    -100,
                );
            }

            return;
        }

        $builder->add($name, $fieldClass, array_merge(['required' => $required], $options));
    }
}
