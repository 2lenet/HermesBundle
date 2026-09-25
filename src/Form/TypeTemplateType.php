<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Form;

use Lle\HermesBundle\Entity\TypeTemplate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TypeTemplateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', TextType::class, [
                'label' => 'field.label',
                'attr' => ['class' => 'col-md-6'],
            ])
            ->add('code', TextType::class, [
                'label' => 'field.code',
                'attr' => ['class' => 'col-md-6'],
            ])
            ->add('unsubscriptionsAllowed', CheckboxType::class, [
                'required' => false,
                'label' => 'field.unsubscriptionsallowed',
                'help' => 'field.unsubscriptionsallowed_help',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TypeTemplate::class,
            'translation_domain' => 'LleHermesBundle',
        ]);
    }

    public function getName(): string
    {
        return 'type_template_form';
    }
}
