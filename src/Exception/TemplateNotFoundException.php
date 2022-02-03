<?php

namespace Lle\HermesBundle\Exception;

class TemplateNotFoundException extends \Exception
{
    public function __construct(string $code)
    {
        parent::__construct("Hermès template '$code' was not found");
    }
}
