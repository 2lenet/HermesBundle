<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Form;

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
        $builder->add('libelle', TextType::class);
        $builder->add('code', TextType::class);
        $builder->add('subject', TextType::class);
        $builder->add('senderName', TextType::class);
        $builder->add('senderEmail', EmailType::class);
        $builder->add('mjml', MjmlType::class);
        $builder->add('text', TextareaType::class);
        $builder->add('unsubscriptions', CheckboxType::class, [
            "required" => false,
            "label" => "field.unsubscriptions",
            "translation_domain" => "LleHermesBundle"
        ]);
        $builder->add('statistics', CheckboxType::class, [
            "required" => false,
            "label" => "field.statistics",
            "translation_domain" => "LleHermesBundle"
        ]);
    }

    public function getName(): string
    {
        return 'template_form';
    }
}
