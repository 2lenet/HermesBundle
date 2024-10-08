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

class TemplateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('groupInformations', GroupType::class, [
            'label' => 'field.group.template_informations',
            'inherit_data' => true,
        ])
            ->add('libelle', TextType::class, [
                'attr' => ['class' => 'col-md-6'],
            ])
            ->add('code', TextType::class, [
                'attr' => ['class' => 'col-md-6'],
            ])
            ->add('senderName', TextType::class, [
                'attr' => ['class' => 'col-md-6'],
            ])
            ->add('senderEmail', EmailType::class, [
                'attr' => ['class' => 'col-md-6'],
            ]);

        $builder->add('groupContent', GroupType::class, [
            'label' => 'field.group.template_content',
            'inherit_data' => true,
        ])
            ->add('subject', TextType::class)
            ->add('html', CKEditorType::class, [
                'config' => [
                    'toolbar' => 'full',
                    'fullPage' => true,
                    'allowedContent' => true,
                    'versionCheck' => false
                ],
                'label' => false,
                'attr' => [
                    'rows' => 20,
                ],
            ])
            ->add('text', TextareaType::class, [
                'attr' => [
                    'rows' => 20,
                ],
            ]);

        $builder->add('groupOptions', GroupType::class, [
            'label' => 'field.group.template_options',
            'inherit_data' => true,
        ])
            ->add('unsubscriptions', CheckboxType::class, [
                "required" => false,
                "label" => "field.unsubscriptions",
                "translation_domain" => "LleHermesBundle",
            ])
            ->add('statistics', CheckboxType::class, [
                "required" => false,
                "label" => "field.statistics",
                "translation_domain" => "LleHermesBundle",
            ])
            ->add('sendToErrors', CheckboxType::class, [
                'required' => false,
                'label' => 'field.sendtoerrors',
            ])
            ->add('customBounceEmail', TextType::class, [
                'required' => false,
                'label' => 'field.custombounceemail',
            ]);
    }

    public function getName(): string
    {
        return 'template_form';
    }
}
