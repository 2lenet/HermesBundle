<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Tests\Validator;

use Lle\HermesBundle\Twig\MailSecurityPolicyFactory;
use Lle\HermesBundle\Twig\MailTwigEnvironment;
use Lle\HermesBundle\Validator\TwigSyntax;
use Lle\HermesBundle\Validator\TwigSyntaxValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigSyntaxValidatorTest extends TestCase
{
    private const TEMPLATE_USING_DATA = <<<'TWIG'
        {% for stay in stays %}{% if stay.nights > 1 %}{{ stay.room|upper }}{% endif %}{% endfor %}
        {{ booking.arrival|date('d/m/Y') }} {{ booking.reference }}
        TWIG;

    public function testAcceptsATemplateWithAllowedConstructs(): void
    {
        $violations = $this->validate(self::TEMPLATE_USING_DATA);

        self::assertCount(0, $violations);
    }

    public function testIgnoresEmptyValues(): void
    {
        $violations = $this->validate('');

        self::assertCount(0, $violations);
    }

    public function testRejectsASyntaxError(): void
    {
        $violations = $this->validate('{% if booking %}unclosed');

        self::assertCount(1, $violations);
        self::assertSame((new TwigSyntax())->message, $violations->get(0)->getMessageTemplate());
    }

    public function testRejectsAForbiddenConstruct(): void
    {
        $violations = $this->validate("{{ ['id']|map(value => value)|join }}");

        self::assertCount(1, $violations);
        self::assertSame((new TwigSyntax())->forbiddenMessage, $violations->get(0)->getMessageTemplate());
        self::assertSame(
            [TwigSyntaxValidator::ERROR_PARAMETER => 'Filter "map" is not allowed.'],
            $violations->get(0)->getParameters(),
        );
    }

    public function testRejectsAForbiddenTagHiddenAfterTheData(): void
    {
        $violations = $this->validate(self::TEMPLATE_USING_DATA . "{% include 'base.html.twig' %}");

        self::assertCount(1, $violations);
        self::assertSame(
            [TwigSyntaxValidator::ERROR_PARAMETER => 'Tag "include" is not allowed.'],
            $violations->get(0)->getParameters(),
        );
    }

    public function testAcceptsATemplateFailingOnlyForLackOfData(): void
    {
        $violations = $this->validate('{{ needs_data(stays) }}');

        self::assertCount(0, $violations);
    }

    private function validate(string $template): ConstraintViolationListInterface
    {
        $dataHungryFunction = new class extends AbstractExtension {
            public function getFunctions(): array
            {
                return [new TwigFunction('needs_data', static fn (array $data): int => count($data))];
            }
        };
        $policy = MailSecurityPolicyFactory::create([], [], ['needs_data']);
        $twigSyntaxValidator = new TwigSyntaxValidator(new MailTwigEnvironment([$dataHungryFunction], $policy));
        $validator = Validation::createValidatorBuilder()
            ->setConstraintValidatorFactory(new ConstraintValidatorFactory([
                TwigSyntaxValidator::class => $twigSyntaxValidator,
            ]))
            ->getValidator();

        return $validator->validate($template, new TwigSyntax());
    }
}
