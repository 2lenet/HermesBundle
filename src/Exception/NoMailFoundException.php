<?php

namespace Lle\HermesBundle\Exception;

class NoMailFoundException extends \Exception
{
    public function __construct(int $id)
    {
        parent::__construct("No email found for recipient $id");
    }
}
