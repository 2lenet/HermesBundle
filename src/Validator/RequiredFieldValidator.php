<?php

namespace Lle\HermesBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class RequiredFieldValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof RequiredField) {
            throw new UnexpectedTypeException($constraint, RequiredField::class);
        }

        if ($value !== null && (!is_string($value) || trim($value) !== '')) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setTranslationDomain('LleHermesBundle')
            ->addViolation();
    }
}
