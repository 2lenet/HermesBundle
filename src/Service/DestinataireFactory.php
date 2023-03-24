<?php

namespace Lle\HermesBundle\Service;

use Lle\HermesBundle\Entity\Recipient;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Model\ContactDto;

class DestinataireFactory
{
    public function createDestinataireFromData(ContactDto $contactDto): Recipient
    {
        $recipient = new Recipient();
        $recipient->setToEmail($contactDto->getAddress());
        $recipient->setToName($contactDto->getName());
        $recipient->setData($contactDto->getData());
        $recipient->setStatus(Recipient::STATUS_SENDING);
        $recipient->setNbRetry(0);

        return $recipient;
    }
}
