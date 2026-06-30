<?php

namespace Lle\HermesBundle\Form;

use Lle\CruditBundle\Form\Type\CKEditorType;
use Lle\CruditBundle\Form\Type\GroupType;
use Lle\HermesBundle\Entity\Template;
use Lle\HermesBundle\Form\Type\MjmlType;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TemplateType extends AbstractType
{
    use TranslatableFieldsTrait;

    public function __construct(
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

        $builder
            ->add('groupInformations', GroupType::class, [
                'label' => 'field.group.template_informations',
                'inherit_data' => true,
            ]);

        $this->addTranslatable($builder, 'libelle', TextType::class, [
            'attr' => ['class' => 'col-md-6'],
        ], required: true);

        $builder->add('code', TextType::class, [
            'attr' => ['class' => 'col-md-6'],
        ]);

        $this->addTranslatable($builder, 'senderName', TextType::class, [
            'attr' => ['class' => 'col-md-6'],
        ]);
        $this->addTranslatable($builder, 'senderEmail', EmailType::class, [
            'attr' => ['class' => 'col-md-6'],
        ], required: true);

        $builder->add('groupContent', GroupType::class, [
            'label' => 'field.group.template_content',
            'inherit_data' => true,
        ]);

        $this->addTranslatable($builder, 'subject', TextType::class, required: true);

        switch ($templateType) {
            case Template::TYPE_CKEDITOR:
                $this->addTranslatable($builder, 'html', CKEditorType::class, [
                    'attr' => ['rows' => 20],
                ]);
                break;
            case Template::TYPE_MJML:
                $this->addTranslatable($builder, 'mjml', MjmlType::class);
                break;
            case Template::TYPE_HTML:
            default:
                $this->addTranslatable($builder, 'html', TextareaType::class, [
                    'attr' => ['rows' => 20],
                ]);
                break;
        }

        $this->addTranslatable($builder, 'text', TextareaType::class, [
            'attr' => ['rows' => 20],
        ]);

        $builder
            ->add('groupOptions', GroupType::class, [
                'label' => 'field.group.template_options',
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
            ])
            ->add('customBounceEmail', TextType::class, [
                'required' => false,
                'label' => 'field.custombounceemail',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => $this->templateClass]);
    }

    public function getName(): string
    {
        return 'template_form';
    }
}
