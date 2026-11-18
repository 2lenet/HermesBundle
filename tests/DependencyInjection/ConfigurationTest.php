<?php

namespace App\Tests\DependencyInjection;

use Lle\HermesBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    private const REQUIRED = [
        'root_dir' => '/tmp',
        'app_secret' => 'secret',
        'app_domain' => 'example.test',
        'bounce_host' => 'host',
        'bounce_port' => '993',
        'bounce_user' => 'user',
        'bounce_password' => 'password',
    ];

    /**
     * @param array<string, mixed> $extra
     *
     * @return array<string, mixed>
     */
    private function process(array $extra = []): array
    {
        return (new Processor())->processConfiguration(new Configuration(), [self::REQUIRED + $extra]);
    }

    public function testMenuIconsAreOnAndImagesByDefault(): void
    {
        $config = $this->process();

        self::assertTrue($config['menu_icons']);
        self::assertSame('image', $config['menu_icon_style']);
    }

    public function testMenuIconsAcceptBooleans(): void
    {
        self::assertFalse($this->process(['menu_icons' => false])['menu_icons']);
        self::assertTrue($this->process(['menu_icons' => true])['menu_icons']);
    }

    /**
     * A quoted "false" used to be accepted by the scalar node and read as true.
     */
    public function testQuotedBooleansAreReadAsWhatTheySpell(): void
    {
        self::assertFalse($this->process(['menu_icons' => 'false'])['menu_icons']);
        self::assertFalse($this->process(['menu_icons' => '0'])['menu_icons']);
        self::assertTrue($this->process(['menu_icons' => 'true'])['menu_icons']);
    }

    public function testAnythingElseIsRefused(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->process(['menu_icons' => 'maybe']);
    }

    public function testUnknownIconStyleIsRefused(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->process(['menu_icon_style' => 'emoji']);
    }
}
