<?php

namespace Lle\HermesBundle\Form;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Lle\HermesBundle\Entity\Template;
use Lle\HermesBundle\Form\Type\MjmlType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class MailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $templateType = $options['data']->getTemplate()?->getType();

        $builder->add('subject', TextType::class, [
            "label" => "field.subject",
            "translation_domain" => "LleHermesBundle",
        ]);

        switch ($templateType) {
            case Template::TYPE_CKEDITOR:
                $builder->add('html', CKEditorType::class, [
                    'label' => false,
                    'config' => ['toolbar' => 'full'],
                    'attr' => [
                        'rows' => 20,
                    ],
                ]);
                break;
            case Template::TYPE_MJML:
                $builder->add('mjml', MjmlType::class);
                break;
            case Template::TYPE_HTML:
            default:
                $builder->add('html', TextareaType::class, [
                    'attr' => [
                        'rows' => 20,
                    ],
                ]);
                break;
        }

        $builder->add('text', TextType::class, [
            "label" => "field.text",
            "translation_domain" => "LleHermesBundle",
        ]);
    }


    public function getName(): string
    {
        return 'mail_form';
    }
}
