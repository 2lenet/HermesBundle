<?php

namespace Lle\HermesBundle\Exception;

class NoRecipientException extends \Exception
{
    public function __construct(int $id)
    {
        parent::__construct("No recipient found for email number $id");
    }
}
