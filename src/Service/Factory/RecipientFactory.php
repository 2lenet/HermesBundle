<?php

namespace Lle\HermesBundle\Service\Factory;

use Lle\HermesBundle\Entity\Recipient;
use Lle\HermesBundle\Model\ContactDto;

class RecipientFactory
{
    public function createRecipientFromDto(ContactDto $contactDto): Recipient
    {
        $recipient = new Recipient();
        $recipient->setToEmail($contactDto->getAddress());
        $recipient->setToName($contactDto->getName());
        $recipient->setData($contactDto->getData());
        $recipient->setStatus(Recipient::STATUS_SENDING);

        return $recipient;
    }
}
