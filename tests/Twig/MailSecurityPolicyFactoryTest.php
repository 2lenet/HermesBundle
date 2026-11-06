<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Tests\Twig;

use Lle\HermesBundle\Twig\MailSecurityPolicyFactory;
use Lle\HermesBundle\Twig\MailTwigEnvironment;
use PHPUnit\Framework\TestCase;
use Twig\Extension\AbstractExtension;
use Twig\Extra\Intl\IntlExtension;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Sandbox\SecurityNotAllowedMethodError;
use Twig\Sandbox\SecurityNotAllowedPropertyError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedTestError;
use Twig\TwigFilter;
use Twig\TwigFunction;

class MailSecurityPolicyFactoryTest extends TestCase
{
    public function testRendersTheConstructsUsedByExistingTemplates(): void
    {
        $twig = $this->sandboxedEnvironment();
        $template = implode('', [
            '{% set total = 0 %}{% for stay in stays %}',
            '{% if stay.nights > 1 %}{{ stay.room|upper }}',
            '{% elseif stay.nights == 1 %}{{ stay.room|lower }}',
            '{% else %}-{% endif %}{% endfor %}',
            "{{ booking.arrival|date('d/m/Y') }} {{ booking.note|default('none') }} {{ stays|length }} ",
            "{{ amount|format_currency('EUR', locale='fr') }} {{ html|raw }} {{ name }}",
        ]);

        $result = $twig->createTemplate($template)->render([
            'stays' => [['room' => 'Suite', 'nights' => 2], ['room' => 'ECO', 'nights' => 1]],
            'booking' => ['arrival' => '2026-11-06', 'note' => null],
            'amount' => 12.5,
            'html' => '<b>ok</b>',
            'name' => 'Tom & Co',
        ]);

        self::assertSame("SUITEeco06/11/2026 none 2 12,50\u{a0}€ <b>ok</b> Tom &amp; Co", $result);
    }

    public function testAllowsUsualTests(): void
    {
        $twig = $this->sandboxedEnvironment();

        $result = $twig->createTemplate('{{ a is defined ? 1 : 0 }}{{ b is empty ? 1 : 0 }}{{ c is null ? 1 : 0 }}')
            ->render(['a' => 1, 'b' => [], 'c' => null]);

        self::assertSame('111', $result);
    }

    public function testRefusesFunctionsOutsideTheList(): void
    {
        $twig = $this->sandboxedEnvironment();
        $template = $twig->createTemplate("{{ source('base.html.twig') }}");

        $this->expectException(SecurityNotAllowedFunctionError::class);

        $template->render([]);
    }

    public function testRefusesFiltersOutsideTheList(): void
    {
        $twig = $this->sandboxedEnvironment();
        $template = $twig->createTemplate("{{ ['id']|map(value => value)|join }}");

        $this->expectException(SecurityNotAllowedFilterError::class);

        $template->render([]);
    }

    public function testRefusesTagsOutsideTheList(): void
    {
        $twig = $this->sandboxedEnvironment();
        $template = $twig->createTemplate("{% include 'base.html.twig' %}");

        $this->expectException(SecurityNotAllowedTagError::class);

        $template->render([]);
    }

    public function testRefusesTheConstantTest(): void
    {
        $twig = $this->sandboxedEnvironment();
        $template = $twig->createTemplate("{{ value is constant('PHP_VERSION') }}");

        $this->expectException(SecurityNotAllowedTestError::class);

        $template->render([]);
    }

    public function testRefusesPropertyAccessOnObjects(): void
    {
        $twig = $this->sandboxedEnvironment();
        $template = $twig->createTemplate('{{ user.password }}');
        $data = ['user' => new class {
            public function getPassword(): string
            {
                return 'secret';
            }
        }];

        $this->expectException(SecurityNotAllowedPropertyError::class);

        $template->render($data);
    }

    public function testRefusesMethodCallsOnObjects(): void
    {
        $twig = $this->sandboxedEnvironment();
        $template = $twig->createTemplate('{{ user.getPassword() }}');
        $data = ['user' => new class {
            public function getPassword(): string
            {
                return 'secret';
            }
        }];

        $this->expectException(SecurityNotAllowedMethodError::class);

        $template->render($data);
    }

    public function testListsCanBeCompletedByTheProject(): void
    {
        $extension = new class extends AbstractExtension {
            public function getFilters(): array
            {
                return [new TwigFilter('shout', static fn (string $value): string => $value . '!')];
            }

            public function getFunctions(): array
            {
                return [new TwigFunction('greeting', static fn (): string => 'hello')];
            }
        };
        $policy = MailSecurityPolicyFactory::create(['apply'], ['shout'], ['greeting']);
        $twig = new MailTwigEnvironment([$extension], $policy);

        $result = $twig->createTemplate('{% apply upper %}{{ greeting()|shout }}{% endapply %}')->render([]);

        self::assertSame('HELLO!', $result);
    }

    public function testDefaultListsAreKeptWhenCompleted(): void
    {
        $policy = MailSecurityPolicyFactory::create([], ['shout'], []);
        $twig = new MailTwigEnvironment([], $policy);

        self::assertSame('A', $twig->createTemplate("{{ 'a'|upper }}")->render([]));
    }

    private function sandboxedEnvironment(): MailTwigEnvironment
    {
        return new MailTwigEnvironment([new IntlExtension()], MailSecurityPolicyFactory::create());
    }
}
