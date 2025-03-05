<?php

namespace Lle\HermesBundle\Validator;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[\Attribute]
class TwigSyntax extends Constraint
{
    public string $message = 'validator.twig_syntax.error';

    #[HasNamedArguments]
    public function __construct(
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct([], $groups, $payload);
    }
}
