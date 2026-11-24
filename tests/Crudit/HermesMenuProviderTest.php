<?php

namespace App\Tests\Crudit;

use Lle\CruditBundle\Dto\Icon;
use Lle\CruditBundle\Dto\Layout\LinkElement;
use Lle\HermesBundle\Crudit\CrudMenu\HermesMenuProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class HermesMenuProviderTest extends TestCase
{
    /**
     * @return list<Icon|null> icon of the main entry, then of each sub-entry
     */
    private function icons(mixed $menuIcons, ?string $style): array
    {
        $parameters = ['lle_hermes.menu_icons' => $menuIcons, 'lle_hermes.tenant_class' => null];
        if (null !== $style) {
            $parameters['lle_hermes.menu_icon_style'] = $style;
        }
        $menu = [...(new HermesMenuProvider(new ParameterBag($parameters)))->getMenuEntry()];
        $root = $menu[0];
        self::assertInstanceOf(LinkElement::class, $root);

        $icons = [$root->getIcon()];
        foreach ($root->getChildren() as $child) {
            self::assertInstanceOf(LinkElement::class, $child);
            $icons[] = $child->getIcon();
        }

        return $icons;
    }

    /**
     * @return list<Icon> the icons, all of which must exist
     */
    private function existingIcons(mixed $menuIcons, ?string $style): array
    {
        $icons = [];
        foreach ($this->icons($menuIcons, $style) as $icon) {
            self::assertInstanceOf(Icon::class, $icon);
            $icons[] = $icon;
        }

        return $icons;
    }

    public function testImagesRemainTheDefault(): void
    {
        // What the extension sets when a project does not configure the style.
        foreach ($this->existingIcons(true, HermesMenuProvider::ICON_STYLE_IMAGE) as $icon) {
            self::assertTrue($icon->isImg());
            self::assertSame('/bundles/llehermes/img/hermes.svg', $icon->getIcon());
        }
    }

    /**
     * Safety net only: the extension always defines the parameter, but a container built without it must not fail.
     */
    public function testAMissingStyleParameterFallsBackToImages(): void
    {
        foreach ($this->existingIcons(true, null) as $icon) {
            self::assertTrue($icon->isImg());
        }
    }

    public function testFontAwesomeKeepsTheLogoOnTheMainEntryAndGivesEachOtherEntryItsOwnIcon(): void
    {
        $icons = $this->existingIcons(true, HermesMenuProvider::ICON_STYLE_FONTAWESOME);
        $main = $icons[0];
        $subEntryIcons = array_slice($icons, 1);

        self::assertTrue($main->isImg());
        self::assertSame('/bundles/llehermes/img/hermes-symbole.png', $main->getIcon());
        foreach ($subEntryIcons as $icon) {
            self::assertFalse($icon->isImg());
            self::assertSame(Icon::TYPE_FA, $icon->getType());
        }
        $names = array_map(static fn (Icon $icon): string => $icon->getIcon(), $subEntryIcons);
        self::assertSame($names, array_unique($names), 'every entry has its own icon');
    }

    public function testNoIconWhenIconsAreDisabled(): void
    {
        foreach ([HermesMenuProvider::ICON_STYLE_IMAGE, HermesMenuProvider::ICON_STYLE_FONTAWESOME] as $style) {
            foreach ($this->icons(false, $style) as $icon) {
                self::assertNull($icon);
            }
        }
    }

    public function testEntriesAreTranslatedInTheBundleDomain(): void
    {
        $parameters = new ParameterBag(['lle_hermes.menu_icons' => false, 'lle_hermes.tenant_class' => null]);
        $root = [...(new HermesMenuProvider($parameters))->getMenuEntry()][0];
        self::assertInstanceOf(LinkElement::class, $root);

        self::assertSame('LleHermesBundle', $root->getTranslationDomain());
        self::assertNotEmpty($root->getChildren());
        foreach ($root->getChildren() as $child) {
            self::assertInstanceOf(LinkElement::class, $child);
            self::assertSame('LleHermesBundle', $child->getTranslationDomain());
            self::assertSame('menu.' . $child->getId(), $child->getLibelle());
        }
    }
}
