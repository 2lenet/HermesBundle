<?php

namespace Lle\HermesBundle\Form;

use Lle\CruditBundle\Filter\FilterType\EntityFilterType;
use Lle\HermesBundle\Entity\Template;
use Lle\HermesBundle\Repository\TemplateRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModelCodeType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => Template::class,
            'query_builder' => function (TemplateRepository $er) {
                return $er->createQueryBuilder('e')
                    ->orderBy('e.code', 'ASC');
            },
            'choice_label' => 'code',
        ]);
    }


    public function getParent()
    {
        return EntityFilterType::class;
    }
}
