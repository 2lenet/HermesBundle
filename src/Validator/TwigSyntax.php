<?php

namespace Lle\HermesBundle\Validator;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[\Attribute]
class TwigSyntax extends Constraint
{
    public string $message = 'validator.twig_syntax.error';

    /** Construct refused by the mail sandbox; `{{ error }}` names it. */
    public string $forbiddenMessage = 'validator.twig_syntax.forbidden';

    #[HasNamedArguments]
    public function __construct(
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct([], $groups, $payload);
    }
}
