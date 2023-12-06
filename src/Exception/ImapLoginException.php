<?php

namespace Lle\HermesBundle\Exception;

class ImapLoginException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Unable to connect to IMAP server');
    }
}
