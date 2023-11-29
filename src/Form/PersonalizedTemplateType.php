<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Form;

use Lle\CruditBundle\Form\Type\GroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class PersonalizedTemplateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('groupContent', GroupType::class, [
            'label' => 'field.group.template_content',
            'inherit_data' => true,
        ])
            ->add('subject', TextType::class)
            ->add('html', TextareaType::class, [
                'attr' => [
                    'rows' => 20,
                ],
            ])
            ->add('text', TextareaType::class, [
                'attr' => [
                    'rows' => 20,
                ],
            ]);
    }

    public function getName(): string
    {
        return 'personalized_template_form';
    }
}
