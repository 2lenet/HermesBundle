<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Form;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Lle\CruditBundle\Form\Type\GroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class TypeTemplateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', TextType::class, [
                'label' => 'field.label',
                'translation_domain' => 'LleHermesBundle',
            ])
            ->add('code', TextType::class, [
                'label' => 'field.code',
                'translation_domain' => 'LleHermesBundle',
            ])
            ->add('unsubscriptionsAllowed', CheckboxType::class, [
                'label' => 'field.unsubscriptionsallowed',
                'translation_domain' => 'LleHermesBundle',
            ]);
    }

    public function getName(): string
    {
        return 'type_template_form';
    }
}
