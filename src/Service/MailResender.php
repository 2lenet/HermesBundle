<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Entity\Recipient;
use Lle\HermesBundle\Service\Factory\RecipientFactory;

class MailResender
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected RecipientFactory $recipientFactory,
    ) {
    }

    public function resend(Mail $mail): int
    {
        $nb = 0;
        foreach ($mail->getRecipients() as $recipient) {
            if ($recipient->getStatus() !== Recipient::STATUS_ERROR) {
                continue;
            }

            $this->em->persist($this->recipientFactory->copy($recipient));
            $nb++;

            if ($nb % 1000 === 0) {
                $this->em->flush();
            }
        }

        if ($nb > 0) {
            $mail->setStatus(Mail::STATUS_SENDING);
        }

        $this->em->flush();

        return $nb;
    }

    /**
     * @param iterable<Recipient> $recipients
     */
    public function resendRecipients(iterable $recipients): int
    {
        $nb = 0;
        $mailsToRequeue = [];

        foreach ($recipients as $recipient) {
            if ($recipient->getStatus() !== Recipient::STATUS_ERROR) {
                continue;
            }

            $mail = $recipient->getMail() ?? $recipient->getCcMail();
            if (!$mail) {
                continue;
            }

            $this->em->persist($this->recipientFactory->copy($recipient));
            $mailsToRequeue[(int)$mail->getId()] = $mail;
            $nb++;

            if ($nb % 1000 === 0) {
                $this->em->flush();
            }
        }

        foreach ($mailsToRequeue as $mail) {
            $mail->setStatus(Mail::STATUS_SENDING);
        }

        $this->em->flush();

        return $nb;
    }
}
