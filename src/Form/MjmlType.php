<?php

namespace Lle\HermesBundle\Form;

use Lle\HermesBundle\Entity\Template;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class MjmlType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = json_decode($event->getData(), true);

            /** @var FormInterface $parentForm */
            $parentForm = $event->getForm()->getParent();
            /** @var Template $template */
            $template = $parentForm->getData();
            $template->setHtml($data["html"]['html']);

            $event->setData($data["mjml"]);
        });
    }

    public function getParent(): string
    {
        return TextType::class;
    }

    public function getBlockPrefix(): string
    {
        return "lle_hermes_mjml";
    }
}
