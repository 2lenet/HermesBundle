<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Form;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Lle\CruditBundle\Form\Type\GroupType;
use Lle\HermesBundle\Service\MultiTenantManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class PersonalizedTemplateType extends AbstractType
{
    public function __construct(
        protected MultiTenantManager $multiTenantManager,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('groupInformations', GroupType::class, [
            'label' => 'field.group.template_informations',
            'inherit_data' => true,
        ])
            ->add('libelle', GedmoTranslatableType::class, [
                'attr' => ['class' => 'col-md-6'],
            ])
            ->add('code', TextType::class, [
                'attr' => ['class' => 'col-md-6'],
            ])
            ->add('senderName', GedmoTranslatableType::class, [
                'attr' => ['class' => 'col-md-6'],
            ])
            ->add('senderEmail', GedmoTranslatableType::class, [
                'field_class' => EmailType::class,
                'attr' => ['class' => 'col-md-6'],
            ]);

        $builder->add('groupContent', GroupType::class, [
            'label' => 'field.group.template_content',
            'inherit_data' => true,
        ])
            ->add('subject', GedmoTranslatableType::class)
            ->add('html', GedmoTranslatableType::class, [
                'field_class' => TextareaType::class,
                'attr' => [
                    'rows' => 20,
                ],
            ])
            ->add('text', GedmoTranslatableType::class, [
                'field_class' => TextareaType::class,
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
            ])
            ->add('tenantId', HiddenType::class, [
                'data' => $this->multiTenantManager->getTenantId(),
                'label' => false
            ]);
    }

    public function getName(): string
    {
        return 'personalized_template_form';
    }
}
