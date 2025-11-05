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
        $builder->add('subject', TextType::class);
        $builder->add('html', TextareaType::class, [
            'attr' => [
                'rows' => 25,
            ]
        ]);
        $builder->add('text', TextType::class);
    }


    public function getName(): string
    {
        return 'edit_mail_form';
    }
}
