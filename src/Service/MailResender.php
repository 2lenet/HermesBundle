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
            if (!$this->resendRecipient($recipient)) {
                continue;
            }

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
            $mail = $recipient->getMail() ?? $recipient->getCcMail();
            $mailId = $mail?->getId();
            if (!$mail || !$mailId) {
                continue;
            }

            if (!$this->resendRecipient($recipient)) {
                continue;
            }

            $mailsToRequeue[$mailId] = $mail;
            $nb++;

            if ($nb % 1000 === 0) {
                $this->em->flush();
            }
        }

        $nbRequeued = 0;
        foreach ($mailsToRequeue as $mail) {
            $mail->setStatus(Mail::STATUS_SENDING);
            $nbRequeued++;

            if ($nbRequeued % 1000 === 0) {
                $this->em->flush();
            }
        }

        $this->em->flush();

        return $nb;
    }

    private function resendRecipient(Recipient $recipient): bool
    {
        if ($recipient->getStatus() !== Recipient::STATUS_ERROR) {
            return false;
        }

        $this->em->persist($this->recipientFactory->copy($recipient));

        return true;
    }
}
