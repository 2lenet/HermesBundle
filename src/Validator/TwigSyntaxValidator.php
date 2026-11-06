<?php

namespace Lle\HermesBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Twig\Environment;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Sandbox\SecurityError;

/**
 * Compiles a mail template with the mail Twig environment (`MailTwigEnvironment`, wired by the
 * bundle): a syntax error or a construct refused by the sandbox is reported on the field.
 */
class TwigSyntaxValidator extends ConstraintValidator
{
    public const TRANSLATION_DOMAIN = 'LleHermesBundle';
    public const ERROR_PARAMETER = '{{ error }}';

    public function __construct(
        protected Environment $twig
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof TwigSyntax) {
            throw new UnexpectedTypeException($constraint, TwigSyntax::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) to take care of that
        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        try {
            // The sandbox checks every construct of the template before running any of it:
            // rendering without data is what triggers that check.
            $this->twig->createTemplate($value)->render([]);
        } catch (SyntaxError) {
            $this->context->buildViolation($constraint->message)
                ->setTranslationDomain(self::TRANSLATION_DOMAIN)
                ->addViolation();
        } catch (SecurityError $exception) {
            $this->context->buildViolation($constraint->forbiddenMessage)
                ->setParameter(self::ERROR_PARAMETER, $exception->getRawMessage())
                ->setTranslationDomain(self::TRANSLATION_DOMAIN)
                ->addViolation();
        } catch (RuntimeError) {
            // Failing for lack of data (a filter given null…) says nothing about the template.
        }
    }
}
