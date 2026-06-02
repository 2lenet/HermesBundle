<?php

namespace Lle\HermesBundle\Validator;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class RequiredFieldValidator extends ConstraintValidator
{
    public function __construct(
        #[Autowire(param: 'lle_hermes.translatable_mail')]
        private bool $translatableMail = true,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof RequiredField) {
            throw new UnexpectedTypeException($constraint, RequiredField::class);
        }

        if ($this->translatableMail || ($value !== null && (!is_string($value) || trim($value) !== ''))) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setTranslationDomain('LleHermesBundle')
            ->addViolation();
    }
}
