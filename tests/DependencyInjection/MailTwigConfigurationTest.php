<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Tests\DependencyInjection;

use Lle\HermesBundle\DependencyInjection\LleHermesExtension;
use Lle\HermesBundle\Service\MailBuilder;
use Lle\HermesBundle\Twig\HermesConsentExtension;
use Lle\HermesBundle\Twig\HermesExtension;
use Lle\HermesBundle\Twig\MailSecurityPolicyFactory;
use Lle\HermesBundle\Twig\MailTwigEnvironment;
use Lle\HermesBundle\Validator\TwigSyntaxValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class MailTwigConfigurationTest extends TestCase
{
    private const REQUIRED_CONFIG = [
        'root_dir' => '/app',
        'app_secret' => 'secret',
        'app_domain' => 'example.com',
        'bounce_host' => 'localhost',
        'bounce_port' => 993,
        'bounce_user' => 'bounce',
        'bounce_password' => 'password',
    ];

    public function testMailTwigEnvironmentGetsHermesAndOptionalSymfonyExtensions(): void
    {
        $container = $this->load([]);

        $extensions = $container->getDefinition(MailTwigEnvironment::class)->getArgument(0);

        self::assertEquals([
            new Reference(HermesConsentExtension::class),
            new Reference('twig.extension.routing', ContainerInterface::IGNORE_ON_INVALID_REFERENCE),
            new Reference('twig.extension.trans', ContainerInterface::IGNORE_ON_INVALID_REFERENCE),
            new Reference('twig.extension.intl', ContainerInterface::IGNORE_ON_INVALID_REFERENCE),
        ], $extensions);
    }

    public function testProjectExtensionsAreRequired(): void
    {
        $container = $this->load(['mail_twig' => ['extensions' => ['App\Twig\ConfigExtension']]]);

        $extensions = $container->getDefinition(MailTwigEnvironment::class)->getArgument(0);

        self::assertEquals(new Reference('App\Twig\ConfigExtension'), end($extensions));
    }

    public function testSandboxIsEnabledByDefault(): void
    {
        $container = $this->load([]);

        $policy = $container->getDefinition(MailTwigEnvironment::class)->getArgument(1);

        self::assertInstanceOf(Definition::class, $policy);
        self::assertSame([MailSecurityPolicyFactory::class, 'create'], $policy->getFactory());
        self::assertSame([[], [], []], $policy->getArguments());
    }

    public function testSandboxListsAreCompletedFromConfiguration(): void
    {
        $container = $this->load(['mail_twig' => ['sandbox' => [
            'allowed_tags' => ['apply'],
            'allowed_filters' => ['markdown'],
            'allowed_functions' => ['get_config_value'],
        ]]]);

        $policy = $container->getDefinition(MailTwigEnvironment::class)->getArgument(1);

        self::assertSame([['apply'], ['markdown'], ['get_config_value']], $policy->getArguments());
    }

    public function testSandboxCanBeDisabled(): void
    {
        $container = $this->load(['mail_twig' => ['sandbox' => ['enabled' => false]]]);

        self::assertNull($container->getDefinition(MailTwigEnvironment::class)->getArgument(1));
    }

    public function testMailServicesUseTheMailTwigEnvironment(): void
    {
        $container = $this->load([]);
        $expected = new Reference(MailTwigEnvironment::class);

        self::assertEquals($expected, $container->getDefinition(MailBuilder::class)->getArgument('$twig'));
        self::assertEquals($expected, $container->getDefinition(HermesExtension::class)->getArgument('$twig'));
        self::assertEquals($expected, $container->getDefinition(TwigSyntaxValidator::class)->getArgument('$twig'));
    }

    public function testMailTwigEnvironmentIsPubliclyAliased(): void
    {
        $container = $this->load([]);

        $alias = $container->getAlias(LleHermesExtension::MAIL_TWIG_SERVICE);

        self::assertSame(MailTwigEnvironment::class, (string) $alias);
        self::assertTrue($alias->isPublic());
    }

    private function load(array $config): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $extension = new LleHermesExtension();
        $configs = [array_merge(self::REQUIRED_CONFIG, $config)];

        $extension->load($configs, $container);

        return $container;
    }
}
