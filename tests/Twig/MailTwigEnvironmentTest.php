<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Tests\Twig;

use Lle\HermesBundle\Twig\MailTwigEnvironment;
use PHPUnit\Framework\TestCase;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MailTwigEnvironmentTest extends TestCase
{
    public function testRendersTemplateData(): void
    {
        $twig = new MailTwigEnvironment();

        $result = $twig->createTemplate('{{ booking.reference }} - {{ customer.name|upper }}')->render([
            'booking' => ['reference' => 'ABC123'],
            'customer' => ['name' => 'Durand'],
        ]);

        self::assertSame('ABC123 - DURAND', $result);
    }

    public function testUnknownVariablesRenderEmpty(): void
    {
        $twig = new MailTwigEnvironment();

        self::assertSame('[]', $twig->createTemplate('[{{ unknown.variable }}]')->render([]));
    }

    public function testApplicationGlobalsAreNotAvailable(): void
    {
        $twig = new MailTwigEnvironment();

        self::assertSame('[]', $twig->createTemplate("[{{ app.request.server.get('APP_SECRET') }}]")->render([]));
    }

    public function testCannotIncludeApplicationTemplates(): void
    {
        $twig = new MailTwigEnvironment();
        $template = "{{ include('base.html.twig') }}";

        $this->expectException(LoaderError::class);

        $twig->createTemplate($template)->render([]);
    }

    public function testRegistersGivenExtensions(): void
    {
        $extension = new class extends AbstractExtension {
            public function getFilters(): array
            {
                return [new TwigFilter('shout', static fn (string $value): string => $value . '!')];
            }
        };
        $twig = new MailTwigEnvironment([$extension]);

        self::assertSame('hello!', $twig->createTemplate("{{ 'hello'|shout }}")->render([]));
    }

    public function testFunctionsOfUnregisteredExtensionsAreUnknown(): void
    {
        $twig = new MailTwigEnvironment();

        $this->expectException(SyntaxError::class);

        $twig->createTemplate("{{ get_config_value('String', 'Design', 'Logo Url', '') }}");
    }
}
