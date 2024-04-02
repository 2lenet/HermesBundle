<?php

namespace Lle\HermesBundle\Exception;

class AttachmentCreationException extends \Exception
{
    public function __construct(string $attachmentName)
    {
        parent::__construct("Can't create attachment '$attachmentName'");
    }
}
