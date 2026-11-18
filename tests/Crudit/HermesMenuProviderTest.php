<?php

namespace App\Tests\Crudit;

use Lle\CruditBundle\Dto\Icon;
use Lle\HermesBundle\Crudit\CrudMenu\HermesMenuProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class HermesMenuProviderTest extends TestCase
{
    /**
     * @return list<Icon|null> icon of the root entry then of each child
     */
    private function icons(mixed $menuIcons, ?string $style): array
    {
        $parameters = ['lle_hermes.menu_icons' => $menuIcons, 'lle_hermes.tenant_class' => null];
        if (null !== $style) {
            $parameters['lle_hermes.menu_icon_style'] = $style;
        }
        $menu = (new HermesMenuProvider(new ParameterBag($parameters)))->getMenuEntry();
        $root = $menu[0];

        return array_merge([$root->getIcon()], array_map(fn ($child) => $child->getIcon(), $root->getChildren()));
    }

    public function testImagesRemainTheDefaultForExistingProjects(): void
    {
        // A project that never set the style: the parameter does not even exist in its container.
        foreach ($this->icons(true, null) as $icon) {
            self::assertTrue($icon->isImg());
            self::assertSame('/bundles/llehermes/img/hermes.svg', $icon->getIcon());
        }
    }

    public function testFontAwesomeKeepsTheLogoOnTheMainEntryAndGivesEachOtherEntryItsOwnIcon(): void
    {
        $icons = $this->icons(true, HermesMenuProvider::ICON_STYLE_FONTAWESOME);
        $main = array_shift($icons);

        self::assertTrue($main->isImg());
        self::assertSame('/bundles/llehermes/img/hermes-symbole.png', $main->getIcon());
        foreach ($icons as $icon) {
            self::assertFalse($icon->isImg());
            self::assertSame(Icon::TYPE_FA, $icon->getType());
        }
        $names = array_map(static fn (Icon $icon): string => $icon->getIcon(), $icons);
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
}
