<?php

namespace Lle\HermesBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Lle\HermesBundle\Twig\MailTwigEnvironment;
use Twig\Error\SyntaxError;
use Twig\Sandbox\SecurityError;

/**
 * Checks a mail template with the mail Twig environment (`MailTwigEnvironment`, wired by the
 * bundle) without running it: a syntax error or a construct refused by the sandbox is reported
 * on the field. Errors that only happen at rendering (an unknown route given to `url()`, a filter
 * given null…) are reported when the mail is sent, on the recipient.
 */
class TwigSyntaxValidator extends ConstraintValidator
{
    public const TRANSLATION_DOMAIN = 'LleHermesBundle';
    public const ERROR_PARAMETER = '{{ error }}';

    public function __construct(
        protected MailTwigEnvironment $twig
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
            $this->twig->checkTemplate($value);
        } catch (SyntaxError) {
            $this->context->buildViolation($constraint->message)
                ->setTranslationDomain(self::TRANSLATION_DOMAIN)
                ->addViolation();
        } catch (SecurityError $exception) {
            $this->context->buildViolation($constraint->forbiddenMessage)
                ->setParameter(self::ERROR_PARAMETER, $exception->getRawMessage())
                ->setTranslationDomain(self::TRANSLATION_DOMAIN)
                ->addViolation();
        }
    }
}
