<?php

namespace Lle\HermesBundle\Service;


use Lle\HermesBundle\Entity\Recipient;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Model\ContactDto;

class DestinataireFactory
{

    public function createDestinataireFromData(ContactDto $contactDto):?Recipient
    {
        $dest = new Recipient();
        $dest->setToEmail($contactDto->getAddress());
        $dest->setToName($contactDto->getName());
        $dest->setData($contactDto->getData());
        $dest->setStatus('ok');
        $dest->setNbRetry(0);
        return $dest;
    }

}
