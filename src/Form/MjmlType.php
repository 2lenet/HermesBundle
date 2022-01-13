<?php

namespace Lle\HermesBundle\Form;

use Lle\HermesBundle\Entity\Template;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class MjmlType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = json_decode($event->getData(), true);

            /** @var Template $template */
            $template = $event->getForm()->getParent()->getData();
            $template->setHtml($data["html"]);

            $event->setData($data["mjml"]);
        });
    }

    public function getParent()
    {
        return TextType::class;
    }

    public function getBlockPrefix()
    {
        return "lle_hermes_mjml";
    }
}
