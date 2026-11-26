<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Tests\Validator;

use Lle\HermesBundle\Contracts\TemplateInterface;
use Lle\HermesBundle\Entity\Template;
use Lle\HermesBundle\Translatable\TranslatableTemplate;
use Lle\HermesBundle\Twig\MailSecurityPolicyFactory;
use Lle\HermesBundle\Twig\MailTwigEnvironment;
use Lle\HermesBundle\Validator\TwigSyntax;
use Lle\HermesBundle\Validator\TwigSyntaxValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\Validation;

/**
 * Every field rendered by `MailTemplater` carries the Twig constraint.
 */
class TemplateTwigFieldsTest extends TestCase
{
    private const FORBIDDEN = "{{ source('base.html.twig') }}";

    /**
     * @return iterable<string, array{class-string<TemplateInterface>, string}>
     */
    public static function renderedFields(): iterable
    {
        foreach ([Template::class, TranslatableTemplate::class] as $templateClass) {
            foreach (['subject', 'senderName', 'html', 'text'] as $field) {
                yield $templateClass . '::' . $field => [$templateClass, $field];
            }
        }
    }

    /**
     * @param class-string<TemplateInterface> $templateClass
     */
    #[DataProvider('renderedFields')]
    public function testForbiddenConstructIsReportedOnTheField(string $templateClass, string $field): void
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->setConstraintValidatorFactory(new ConstraintValidatorFactory([
                TwigSyntaxValidator::class => new TwigSyntaxValidator(
                    new MailTwigEnvironment([], MailSecurityPolicyFactory::create()),
                ),
            ]))
            ->getValidator();
        $template = new $templateClass();

        $violations = $validator->validatePropertyValue($template, $field, self::FORBIDDEN);

        self::assertCount(1, $violations);
        self::assertSame((new TwigSyntax())->forbiddenMessage, $violations->get(0)->getMessageTemplate());
    }
}
