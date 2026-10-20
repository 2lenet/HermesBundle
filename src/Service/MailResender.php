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
        if (!$mail->canBeResent()) {
            return 0;
        }

        $nb = 0;
        foreach ($mail->getAllRecipients() as $recipient) {
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
     * Resends to a single recipient, whatever its status, unless the mail is a draft or cancelled
     */
    public function forceResend(Recipient $recipient): bool
    {
        $mail = $recipient->getMail() ?? $recipient->getCcMail();
        if (!$mail || !$mail->canResendRecipients()) {
            return false;
        }

        // The original is excluded from the mail totals and no longer sent, the copy takes over
        $recipient->setStatus(Recipient::STATUS_RESENT);
        $this->em->persist($this->recipientFactory->copy($recipient));

        $mail->setStatus(Mail::STATUS_SENDING);
        $this->em->flush();

        return true;
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
            if (!$mail || !$mailId || !$mail->canResendRecipients()) {
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

        $this->em->flush();

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

        $recipient->setStatus(Recipient::STATUS_RESENT);
        $this->em->persist($this->recipientFactory->copy($recipient));

        return true;
    }
}
