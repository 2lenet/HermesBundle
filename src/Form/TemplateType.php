<?php

namespace Lle\HermesBundle\Form;

use Lle\CruditBundle\Form\Type\CKEditorType;
use Lle\CruditBundle\Form\Type\GedmoTranslatableType;
use Lle\CruditBundle\Form\Type\GroupType;
use Lle\HermesBundle\Entity\Template;
use Lle\HermesBundle\Form\Type\MjmlType;
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
        $templateType = $options['data']->getType();

        $builder
            ->add('groupInformations', GroupType::class, [
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
            ->add('senderEmail', EmailType::class, [
                'attr' => ['class' => 'col-md-6'],
            ])
            ->add('groupContent', GroupType::class, [
                'label' => 'field.group.template_content',
                'inherit_data' => true,
            ])
            ->add('subject', GedmoTranslatableType::class);

        switch ($templateType) {
            case Template::TYPE_CKEDITOR:
                $builder->add('html', GedmoTranslatableType::class, [
                    'fields_class' => CKEditorType::class,
                    'label' => false,
                ]);
                break;
            case Template::TYPE_MJML:
                $builder->add('mjml', MjmlType::class);
                break;
            case Template::TYPE_HTML:
            default:
                $builder->add('html', GedmoTranslatableType::class, [
                    'fields_class' => TextareaType::class,
                    'attr' => [
                        'rows' => 20,
                    ],
                ]);
                break;
        }

        $builder
            ->add('text', GedmoTranslatableType::class, [
                'fields_class' => TextareaType::class,
                'attr' => [
                    'rows' => 20,
                ],
            ])
            ->add('groupOptions', GroupType::class, [
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
