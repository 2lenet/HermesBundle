<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;


class TemplateType extends AbstractType
{
    public function __construct(private Security $security, private ParameterBagInterface $parameterBag)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('libelle', TextType::class);
        $builder->add('code', TextType::class);
        $builder->add('subject', TextType::class);
        $builder->add('senderName', TextType::class);
        $builder->add('senderEmail', EmailType::class);
        $builder->add('mjml', MjmlType::class);
        $builder->add('text', TextareaType::class, [
            'attr' => [
                'rows' => 20
            ]
        ]);
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
        if ($this->security->isGranted('ROLE_LLE_HERMES_EDIT_TENANT') && $this->parameterBag->get('lle_hermes.tenant_class')) {
            $builder->add('tenantId', EntityType::class, [
                'class' => $this->parameterBag->get('lle_hermes.tenant_class'),
            ])->setEmptyData(null)->setRequired(false);
        }
    }

    public function getName(): string
    {
        return 'template_form';
    }
}
