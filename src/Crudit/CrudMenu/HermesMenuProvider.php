<?php

namespace Lle\HermesBundle\Crudit\CrudMenu;

use Lle\CruditBundle\Contracts\MenuProviderInterface;
use Lle\CruditBundle\Dto\Icon;
use Lle\CruditBundle\Dto\Layout\LinkElement;
use Lle\CruditBundle\Dto\Path;

/**
 * Class HermesMenuProvider
 * @package Lle\HermesBundle\Crudit\CrudMenu
 *
 * @author 2LE <2le@2le.net>
 */
class HermesMenuProvider implements MenuProviderInterface
{
    public function getMenuEntry(): iterable
    {
        /** @var LinkElement $menu */
        $menu = LinkElement::new(
            'menu.lle_hermes',
            Path::new('lle_hermes_dashboard'),
            Icon::new('/bundles/llehermes/img/hermes.svg', Icon::TYPE_IMG),
        )->setRole('ROLE_LLE_HERMES');

        $menu->addChild(
            LinkElement::new(
                'menu.lle_hermes_dashboard',
                Path::new('lle_hermes_dashboard'),
                Icon::new('/bundles/llehermes/img/hermes.svg', Icon::TYPE_IMG),
                "ROLE_LLE_HERMES"
            )
        );
        $menu->addChild(
            LinkElement::new(
                'menu.lle_hermes_template',
                Path::new('lle_hermes_crudit_template_index'),
                Icon::new('/bundles/llehermes/img/hermes.svg', Icon::TYPE_IMG),
                "ROLE_TEMPLATE_INDEX"
            )
        );
        $menu->addChild(
            LinkElement::new(
                'menu.lle_hermes_mail',
                Path::new('lle_hermes_crudit_mail_index'),
                Icon::new('/bundles/llehermes/img/hermes.svg', Icon::TYPE_IMG),
                "ROLE_MAIL_INDEX"
            )
        );
        $menu->addChild(
            LinkElement::new(
                'menu.lle_hermes_recipient',
                Path::new('lle_hermes_crudit_recipient_index'),
                Icon::new('/bundles/llehermes/img/hermes.svg', Icon::TYPE_IMG),
                "ROLE_RECIPIENT_INDEX"
            )
        );
        return [
            $menu
        ];
    }
}
