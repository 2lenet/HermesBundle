<?php

namespace Lle\HermesBundle\Exception;

class ImapLoginException extends \Exception
{
    public function __construct(int $id)
    {
        parent::__construct('Unable to connect to IMAP server');
    }
}
