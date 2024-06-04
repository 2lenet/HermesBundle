<?php

namespace Lle\HermesBundle\Crudit\CrudMenu;

use Lle\CruditBundle\Contracts\MenuProviderInterface;
use Lle\CruditBundle\Dto\Icon;
use Lle\CruditBundle\Dto\Layout\LinkElement;
use Lle\CruditBundle\Dto\Path;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class HermesMenuProvider
 * @package Lle\HermesBundle\Crudit\CrudMenu
 *
 * @author 2LE <2le@2le.net>
 */
class HermesMenuProvider implements MenuProviderInterface
{
    public function __construct(
        protected readonly ParameterBagInterface $parameters,
    ) {
    }

    public function getMenuEntry(): iterable
    {
        $hasIcons = $this->parameters->get('lle_hermes.menu_icons');

        /** @var LinkElement $menu */
        $menu = LinkElement::new(
            'menu.lle_hermes',
            Path::new('lle_hermes_dashboard'),
            ($hasIcons ? Icon::new('/bundles/llehermes/img/hermes.svg', Icon::TYPE_IMG) : null),
        )->setRole('ROLE_HERMES_DASHBOARD');

        $menu->addChild(
            LinkElement::new(
                'menu.lle_hermes_dashboard',
                Path::new('lle_hermes_dashboard'),
                ($hasIcons ? Icon::new('/bundles/llehermes/img/hermes.svg', Icon::TYPE_IMG) : null),
                "ROLE_HERMES_DASHBOARD"
            )
        );
        $menu->addChild(
            LinkElement::new(
                'menu.lle_hermes_template',
                Path::new('lle_hermes_crudit_template_index'),
                ($hasIcons ? Icon::new('/bundles/llehermes/img/hermes.svg', Icon::TYPE_IMG) : null),
                "ROLE_HERMES_TEMPLATE_INDEX"
            )
        );
        if ($this->parameters->get('lle_hermes.tenant_class')) {
            $menu->addChild(
                LinkElement::new(
                    'menu.lle_hermes_personalized_template',
                    Path::new('lle_hermes_crudit_personalizedtemplate_index'),
                    ($hasIcons ? Icon::new('/bundles/llehermes/img/hermes.svg', Icon::TYPE_IMG) : null),
                    "ROLE_HERMES_TEMPLATE_INDEX"
                )
            );
        }
        $menu->addChild(
            LinkElement::new(
                'menu.lle_hermes_mail',
                Path::new('lle_hermes_crudit_mail_index'),
                ($hasIcons ? Icon::new('/bundles/llehermes/img/hermes.svg', Icon::TYPE_IMG) : null),
                "ROLE_HERMES_MAIL_INDEX"
            )
        );
        $menu->addChild(
            LinkElement::new(
                'menu.lle_hermes_recipient',
                Path::new('lle_hermes_crudit_recipient_index'),
                ($hasIcons ? Icon::new('/bundles/llehermes/img/hermes.svg', Icon::TYPE_IMG) : null),
                "ROLE_HERMES_RECIPIENT_INDEX"
            )
        );
        $menu->addChild(
            LinkElement::new(
                'menu.lle_hermes_emailerror',
                Path::new('lle_hermes_crudit_emailerror_index'),
                ($hasIcons ? Icon::new('/bundles/llehermes/img/hermes.svg', Icon::TYPE_IMG) : null),
                "ROLE_HERMES_EMAILERROR_INDEX"
            )
        );

        return [
            $menu,
        ];
    }
}
