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

        if ($this->translatableMail || ($value !== null && (!is_string($value) || trim($value) !== ''))) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setTranslationDomain('LleHermesBundle')
            ->addViolation();
    }
}
