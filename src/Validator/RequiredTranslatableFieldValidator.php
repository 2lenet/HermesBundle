<?php

namespace Lle\HermesBundle\Validator;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class RequiredTranslatableFieldValidator extends ConstraintValidator
{
    public function __construct(
        #[Autowire(param: 'lle_hermes.translatable_mail')]
        private readonly bool $translatableMail = true,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof RequiredTranslatableField) {
            throw new UnexpectedTypeException($constraint, RequiredTranslatableField::class);
        }

        // When translatable_mail is enabled, the GedmoTranslatableType widget owns the validation:
        // the main property stays null until the Gedmo listener writes the default-locale value
        // on flush, so we cannot rely on it here.
        if ($this->translatableMail) {
            return;
        }

        if ($value !== null && (!is_string($value) || trim($value) !== '')) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setTranslationDomain('LleHermesBundle')
            ->addViolation();
    }
}
