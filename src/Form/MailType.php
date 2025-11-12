<?php

namespace Lle\HermesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class MailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('subject', TextType::class, [
            "label" => "field.subject",
            "translation_domain" => "LleHermesBundle",
        ]);
        $builder->add('html', TextareaType::class, [
            'attr' => [
                'rows' => 25,
            ],
            "label" => "field.html",
            "translation_domain" => "LleHermesBundle",
        ]);
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
