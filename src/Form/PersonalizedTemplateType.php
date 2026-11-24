<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Form;

use Lle\CruditBundle\Form\Type\CKEditorType;
use Lle\CruditBundle\Form\Type\GroupType;
use Lle\HermesBundle\Entity\Template;
use Lle\HermesBundle\Form\Type\MjmlType;
use Lle\HermesBundle\Service\MultiTenantManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonalizedTemplateType extends AbstractType
{
    use TranslatableFieldsTrait;

    public function __construct(
        protected MultiTenantManager $multiTenantManager,
        #[Autowire(param: 'lle_hermes.translatable_mail')]
        bool $translatableMail = true,
        #[Autowire(param: 'lle_hermes.template_class')]
        private string $templateClass = Template::class,
    ) {
        $this->translatableMail = $translatableMail;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $templateType = $options['data']->getType();

        $builder->add('groupInformations', GroupType::class, [
            'label' => 'field.group.template_informations',
            'translation_domain' => 'LleHermesBundle',
            'inherit_data' => true,
        ]);

        $this->addTranslatable($builder, 'libelle', TextType::class, [
            'label' => 'field.libelle',
            'translation_domain' => 'LleHermesBundle',
            'attr' => ['class' => 'col-md-6'],
        ], required: true);

        $builder->add('code', TextType::class, [
            'label' => 'field.code',
            'translation_domain' => 'LleHermesBundle',
            'attr' => ['class' => 'col-md-6'],
        ]);

        $this->addTranslatable($builder, 'senderName', TextType::class, [
            'label' => 'field.sendername',
            'translation_domain' => 'LleHermesBundle',
            'attr' => ['class' => 'col-md-6'],
        ]);
        $this->addTranslatable($builder, 'senderEmail', EmailType::class, [
            'label' => 'field.senderemail',
            'translation_domain' => 'LleHermesBundle',
            'attr' => ['class' => 'col-md-6'],
        ], required: true);

        $builder->add('groupContent', GroupType::class, [
            'label' => 'field.group.template_content',
            'translation_domain' => 'LleHermesBundle',
            'inherit_data' => true,
        ]);

        $this->addTranslatable($builder, 'subject', TextType::class, [
            'label' => 'field.subject',
            'translation_domain' => 'LleHermesBundle',
            'attr' => ['class' => 'col-md-12'],
        ], required: true);

        switch ($templateType) {
            case Template::TYPE_CKEDITOR:
                $this->addTranslatable($builder, 'html', CKEditorType::class, [
                    'label' => 'field.html',
                    'translation_domain' => 'LleHermesBundle',
                    'attr' => ['rows' => 20, 'class' => 'col-md-12'],
                ]);
                break;
            case Template::TYPE_MJML:
                $this->addTranslatable($builder, 'mjml', MjmlType::class, [
                    'label' => 'field.mjml',
                    'translation_domain' => 'LleHermesBundle',
                    'attr' => ['class' => 'col-md-12'],
                ]);
                break;
            case Template::TYPE_HTML:
            default:
                $this->addTranslatable($builder, 'html', TextareaType::class, [
                    'label' => 'field.html',
                    'translation_domain' => 'LleHermesBundle',
                    'attr' => ['rows' => 20, 'class' => 'col-md-12'],
                ]);
                break;
        }

        $builder->add('groupOptions', GroupType::class, [
            'label' => 'field.group.template_options',
            'translation_domain' => 'LleHermesBundle',
            'inherit_data' => true,
        ])
            ->add('unsubscriptions', CheckboxType::class, [
                'required' => false,
                'label' => 'field.unsubscriptions',
                'translation_domain' => 'LleHermesBundle',
            ])
            ->add('statistics', CheckboxType::class, [
                'required' => false,
                'label' => 'field.statistics',
                'translation_domain' => 'LleHermesBundle',
            ])
            ->add('sendToErrors', CheckboxType::class, [
                'required' => false,
                'label' => 'field.sendtoerrors',
                'translation_domain' => 'LleHermesBundle',
            ])
            ->add('customBounceEmail', TextType::class, [
                'required' => false,
                'label' => 'field.custombounceemail',
                'translation_domain' => 'LleHermesBundle',
            ])
            ->add('tenantId', HiddenType::class, [
                'data' => $this->multiTenantManager->getTenantId(),
                'label' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => $this->templateClass]);
    }

    public function getName(): string
    {
        return 'personalized_template_form';
    }
}
