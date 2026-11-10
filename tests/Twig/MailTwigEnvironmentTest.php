<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Tests\Twig;

use Lle\HermesBundle\Twig\MailSecurityPolicyFactory;
use Lle\HermesBundle\Twig\MailTwigEnvironment;
use PHPUnit\Framework\TestCase;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;
use Twig\Extension\AbstractExtension;
use Twig\RuntimeLoader\FactoryRuntimeLoader;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\TwigFilter;
use Twig\TwigFunction;

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

    public function testRendersExtensionsBackedByARuntime(): void
    {
        $runtime = new class {
            public function shout(string $value): string
            {
                return $value . '!';
            }
        };
        $extension = new class ($runtime::class) extends AbstractExtension {
            /**
             * @param class-string $runtimeClass
             */
            public function __construct(private string $runtimeClass)
            {
            }

            public function getFilters(): array
            {
                return [new TwigFilter('shout', [$this->runtimeClass, 'shout'])];
            }
        };
        $runtimeLoader = new FactoryRuntimeLoader([$runtime::class => static fn (): object => $runtime]);
        $twig = new MailTwigEnvironment([$extension], null, $runtimeLoader);

        self::assertSame('hello!', $twig->createTemplate("{{ 'hello'|shout }}")->render([]));
    }

    public function testCheckTemplateRefusesAForbiddenConstructWithoutRunningIt(): void
    {
        $twig = new MailTwigEnvironment([], MailSecurityPolicyFactory::create());
        $template = '{% if false %}{{ stays|map(stay => stay)|join }}{% endif %}';

        $this->expectException(SecurityNotAllowedFilterError::class);

        $twig->checkTemplate($template);
    }

    public function testCheckTemplateRefusesAForbiddenFunction(): void
    {
        $twig = new MailTwigEnvironment([], MailSecurityPolicyFactory::create());

        $this->expectException(SecurityNotAllowedFunctionError::class);

        $twig->checkTemplate("{{ constant('PHP_VERSION') }}");
    }

    public function testCheckTemplateDoesNotRunTheTemplate(): void
    {
        $calls = new \ArrayObject();
        $extension = new class ($calls) extends AbstractExtension {
            public function __construct(private \ArrayObject $calls)
            {
            }

            public function getFunctions(): array
            {
                return [new TwigFunction('track', function (): string {
                    $this->calls->append(true);

                    return '';
                })];
            }
        };
        $twig = new MailTwigEnvironment([$extension], MailSecurityPolicyFactory::create([], [], ['track']));

        $twig->checkTemplate('{{ track() }}');

        self::assertCount(0, $calls);
    }

    public function testCheckTemplateReportsSyntaxErrorsWithoutSandbox(): void
    {
        $twig = new MailTwigEnvironment();

        $this->expectException(SyntaxError::class);

        $twig->checkTemplate('{% if booking %}unclosed');
    }

    public function testCheckTemplateAcceptsAnyConstructWithoutSandbox(): void
    {
        $twig = new MailTwigEnvironment();

        $twig->checkTemplate("{{ constant('PHP_VERSION') }}");

        $this->addToAssertionCount(1);
    }
}
