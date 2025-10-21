<?php

namespace Lle\HermesBundle\Service\Factory;

use Lle\HermesBundle\Entity\Recipient;
use Lle\HermesBundle\Model\ContactDto;

class RecipientFactory
{
    public function createRecipientFromDto(ContactDto $contactDto, ?int $tenantId = null): Recipient
    {
        $recipient = new Recipient();
        $recipient->setToEmail($contactDto->getAddress());
        $recipient->setToName($contactDto->getName());
        $recipient->setData($contactDto->getData());
        $recipient->setStatus(Recipient::STATUS_SENDING);
        $recipient->setTenantId($tenantId);

        return $recipient;
    }

    public function copy(Recipient $recipient): Recipient
    {
        $copy = new Recipient();
        $copy
            ->setToName($recipient->getToName())
            ->setToEmail($recipient->getToEmail())
            ->setData($recipient->getData())
            ->setStatus(Recipient::STATUS_SENDING)
            ->setMail($recipient->getMail())
            ->setCcMail($recipient->getCcMail());

        return $copy;
    }
}
