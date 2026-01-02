<?php

namespace Lle\HermesBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Entity\Recipient;

class MailCanceller
{
    public function __construct(
        protected EntityManagerInterface $em,
    ) {
    }

    public function cancel(Mail $mail): void
    {
        $mail->setStatus(Mail::STATUS_CANCELLED);

        foreach ($mail->getSendingRecipients() as $recipient) {
            $recipient->setStatus(Recipient::STATUS_CANCELLED);
        }

        $this->em->flush();
    }
}
