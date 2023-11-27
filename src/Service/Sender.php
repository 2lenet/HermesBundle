<?php

namespace Lle\HermesBundle\Service;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Entity\Recipient;
use Lle\HermesBundle\Exception\NoMailFoundException;
use Lle\HermesBundle\Repository\RecipientRepository;
use Lle\HermesBundle\Repository\UnsubscribeEmailRepository;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Exception\RfcComplianceException;

/**
 * Class Sender
 * @package Lle\HermesBundle\Service
 *
 * @author 2LE <2le@2le.net>
 */
class Sender
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly MailerInterface $mailer,
        protected readonly MailBuilder $mailBuilder,
        protected readonly RecipientRepository $recipientRepository,
        protected readonly UnsubscribeEmailRepository $unsubscribeEmailRepository,
    ) {
    }

    public function sendAllMails(int $limit = 10): int
    {
        $recipients = $this->recipientRepository->findRecipientsSending(
            Recipient::STATUS_SENDING,
            Mail::STATUS_SENDING,
            $limit
        );

        return $this->sendAllRecipients($recipients);
    }

    /**
     * @param Recipient[] $recipients
     * @throws NoMailFoundException
     */
    public function sendAllRecipients(array $recipients): int
    {
        $unsubscribedArray = $this->unsubscribeEmailRepository->findEmailsUnsubscribed();
        $nb = 0;

        foreach ($recipients as $recipient) {
            $mail = $recipient->getMail() ?? $recipient->getCcMail();
            if (!$mail) {
                throw new NoMailFoundException($recipient->getId());
            }

            $template = $mail->getTemplate();

            // Unsubscriptions are disabled depending on whether the email template takes them into account or not.
            if ($template->isUnsubscriptions()) {
                if (in_array($recipient->getToEmail(), array_column($unsubscribedArray, 'email'))) {
                    $recipient->setStatus(Recipient::STATUS_UNSUBSCRIBED);

                    continue;
                }
            }

            if ($this->send($mail, $recipient)) {
                $nb++;
            } else {
                print("error sending to " . $recipient);
            }

            $this->updateMail($mail);
        }

        return $nb;
    }

    protected function send(Mail $mail, Recipient $recipient, bool $updateSendingDate = true): bool
    {
        try {
            $this->mailer->send($this->mailBuilder->buildMail($mail, $recipient));
            $recipient->setStatus(Recipient::STATUS_SENT);

            if ($updateSendingDate) {
                $mail->setSendingDate(new DateTime());
            }

            return true;
        } catch (TransportExceptionInterface | RfcComplianceException) {
            $recipient->setStatus(Recipient::STATUS_ERROR);

            return false;
        } catch (Exception) {
            $recipient->setStatus(Recipient::STATUS_ERROR);
            $mail->setStatus(Mail::STATUS_ERROR);

            return false;
        }
    }

    protected function updateMail(Mail $mail): void
    {
        $this->em->flush();

        $recipientsSent = $this->recipientRepository
            ->findBy(['status' => Recipient::STATUS_SENT, 'mail' => $mail, 'test' => false]);
        $mail->setTotalSended(count($recipientsSent));

        $unsubscribedMails = $this->recipientRepository
            ->findBy(['status' => Recipient::STATUS_UNSUBSCRIBED, 'mail' => $mail, 'test' => false]);
        $mail->setTotalUnsubscribed(count($unsubscribedMails));

        $errorMails = $this->recipientRepository
            ->findBy(['status' => Recipient::STATUS_ERROR, 'mail' => $mail, 'test' => false]);
        $mail->setTotalError(count($errorMails));

        $totalRecipientsToSend = $mail->getTotalToSend() - $mail->getTotalUnsubscribed();
        $totalRecipientsSended = $mail->getTotalSended() + $mail->getTotalError();

        if ($totalRecipientsSended === $totalRecipientsToSend) {
            $mail->setStatus(Mail::STATUS_SENT);
        } else {
            if ($mail->getTotalError() === $totalRecipientsToSend) {
                $mail->setStatus(Mail::STATUS_ERROR);
            }
        }

        $this->em->flush();
    }

    public function sendRecipient(Recipient $recipient): int
    {
        $mail = $recipient->getMail() ?? $recipient->getCcMail();
        if (!$mail) {
            throw new NoMailFoundException($recipient->getId());
        }

        if ($this->send($mail, $recipient, false)) {
            return 1;
        } else {
            print("error sending to " . $recipient);
        }

        return 0;
    }
}
