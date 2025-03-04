<?php

namespace Lle\HermesBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Twig\Environment;
use Twig\Error\SyntaxError;

class TwigSyntaxValidator extends ConstraintValidator
{
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
            $this->twig->disableStrictVariables();
            $template = $this->twig->createTemplate($value);
            $this->twig->render($template);
            $this->twig->enableStrictVariables();
            return;
        } catch (SyntaxError) {
        }
        
        // the argument must be a string or an object implementing __toString()
        $this->context->buildViolation($constraint->message)
            ->setTranslationDomain('LleHermesBundle')
            ->addViolation();
    }
}
