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
    public const ICON_STYLE_IMAGE = 'image';
    public const ICON_STYLE_FONTAWESOME = 'fontawesome';

    private const IMAGE_ICON = '/bundles/llehermes/img/hermes.svg';
    private const LOGO_ICON = '/bundles/llehermes/img/hermes-symbole.png';

    /** Font Awesome icon of each menu entry (names valid in Font Awesome 5 and 6), used by the "fontawesome" style. */
    private const ICON_DASHBOARD = 'tachometer-alt';
    private const ICON_TYPETEMPLATE = 'tags';
    private const ICON_TEMPLATE = 'file-alt';
    private const ICON_PERSONALIZED = 'file-signature';
    private const ICON_MAIL = 'paper-plane';
    private const ICON_RECIPIENT = 'users';
    private const ICON_EMAILERROR = 'exclamation-triangle';
    private const ICON_UNSUBSCRIBE = 'user-slash';
    private const ICON_CONSENT = 'check-square';

    public function __construct(
        protected readonly ParameterBagInterface $parameters,
    ) {
    }

    /**
     * The "image" style (default, kept for existing projects) shows the Hermes logo on every entry; "fontawesome"
     * shows one Font Awesome icon per sub-entry (see rootIcon() for the main one). `menu_icons: false` removes the
     * icons whatever the style.
     */
    protected function icon(bool $hasIcons, string $fontAwesomeIcon): ?Icon
    {
        if (!$hasIcons) {
            return null;
        }

        if ($this->isFontAwesomeStyle()) {
            return Icon::new($fontAwesomeIcon);
        }

        return Icon::new(self::IMAGE_ICON, Icon::TYPE_IMG);
    }

    /**
     * Icon of the main (dropdown) entry: the Hermes logo, as an image, in both styles.
     */
    protected function rootIcon(bool $hasIcons): ?Icon
    {
        if (!$hasIcons) {
            return null;
        }

        return Icon::new($this->isFontAwesomeStyle() ? self::LOGO_ICON : self::IMAGE_ICON, Icon::TYPE_IMG);
    }

    private function isFontAwesomeStyle(): bool
    {
        // The extension always defines the parameter; the check only protects a container built without it.
        return $this->parameters->has('lle_hermes.menu_icon_style')
            && self::ICON_STYLE_FONTAWESOME === $this->parameters->get('lle_hermes.menu_icon_style');
    }

    public function getMenuEntry(): iterable
    {
        $hasIcons = (bool) $this->parameters->get('lle_hermes.menu_icons');

        /** @var LinkElement $menu */
        $menu = LinkElement::new(
            'menu.lle_hermes',
            Path::new('lle_hermes_dashboard'),
            $this->rootIcon($hasIcons),
        )->setRole('ROLE_HERMES_DASHBOARD');

        $menu->addChild(
            LinkElement::new(
                'menu.lle_hermes_dashboard',
                Path::new('lle_hermes_dashboard'),
                $this->icon($hasIcons, self::ICON_DASHBOARD),
                "ROLE_HERMES_DASHBOARD"
            )
        );
        $menu->addChild(
            LinkElement::new(
                'menu.lle_hermes_typetemplate',
                Path::new('lle_hermes_crudit_typetemplate_index'),
                $this->icon($hasIcons, self::ICON_TYPETEMPLATE),
                'ROLE_HERMES_TYPETEMPLATE_INDEX',
            ),
        );
        $menu->addChild(
            LinkElement::new(
                'menu.lle_hermes_template',
                Path::new('lle_hermes_crudit_template_index'),
                $this->icon($hasIcons, self::ICON_TEMPLATE),
                "ROLE_HERMES_TEMPLATE_INDEX"
            )
        );
        if ($this->parameters->get('lle_hermes.tenant_class')) {
            $menu->addChild(
                LinkElement::new(
                    'menu.lle_hermes_personalized_template',
                    Path::new('lle_hermes_crudit_personalizedtemplate_index'),
                    $this->icon($hasIcons, self::ICON_PERSONALIZED),
                    "ROLE_HERMES_TEMPLATE_INDEX"
                )
            );
        }
        $menu->addChild(
            LinkElement::new(
                'menu.lle_hermes_mail',
                Path::new('lle_hermes_crudit_mail_index'),
                $this->icon($hasIcons, self::ICON_MAIL),
                "ROLE_HERMES_MAIL_INDEX"
            )
        );
        $menu->addChild(
            LinkElement::new(
                'menu.lle_hermes_recipient',
                Path::new('lle_hermes_crudit_recipient_index'),
                $this->icon($hasIcons, self::ICON_RECIPIENT),
                "ROLE_HERMES_RECIPIENT_INDEX"
            )
        );
        $menu->addChild(
            LinkElement::new(
                'menu.lle_hermes_emailerror',
                Path::new('lle_hermes_crudit_emailerror_index'),
                $this->icon($hasIcons, self::ICON_EMAILERROR),
                "ROLE_HERMES_EMAILERROR_INDEX"
            )
        );
        $menu->addChild(
            LinkElement::new(
                'menu.lle_hermes_unsubscribeemail',
                Path::new('lle_hermes_crudit_unsubscribeemail_index'),
                $this->icon($hasIcons, self::ICON_UNSUBSCRIBE),
                'ROLE_HERMES_UNSUBSCRIBEEMAIL_INDEX',
            ),
        );
        $menu->addChild(
            LinkElement::new(
                'menu.lle_hermes_consent',
                Path::new('lle_hermes_crudit_consent_index'),
                $this->icon($hasIcons, self::ICON_CONSENT),
                'ROLE_HERMES_CONSENT_INDEX',
            ),
        );

        return [
            $menu,
        ];
    }
}
