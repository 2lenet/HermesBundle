<?php

namespace Lle\HermesBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class RequiredTranslatableFieldValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof RequiredTranslatableField) {
            throw new UnexpectedTypeException($constraint, RequiredTranslatableField::class);
        }

        if (is_string($value) && trim($value) !== '') {
            return;
        }
        if ($value !== null && !is_string($value)) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setTranslationDomain('LleHermesBundle')
            ->addViolation();
    }
}
