<?php

namespace Lle\HermesBundle\Service;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Entity\Recipient;
use Lle\HermesBundle\Exception\NoMailFoundException;
use Lle\HermesBundle\Repository\EmailErrorRepository;
use Lle\HermesBundle\Repository\RecipientRepository;
use Lle\HermesBundle\Repository\UnsubscribeEmailRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer as SymfonyMailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
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
        protected readonly EmailErrorRepository $emailErrorRepository,
        protected readonly MailerInterface $mailer,
        protected readonly MailBuilder $mailBuilder,
        protected readonly ParameterBagInterface $parameters,
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
        $unsubscribedEmails = $this->getUnsubscribedEmails();
        /** @var int $maxNbRetry */
        $maxNbRetry = $this->parameters->get('lle_hermes.recipient_error_retry');
        $errorArray = $this->emailErrorRepository->findEmailsInError($maxNbRetry);
        $nb = 0;

        foreach ($recipients as $recipient) {
            $mail = $recipient->getMail() ?? $recipient->getCcMail();
            if (!$mail) {
                throw new NoMailFoundException($recipient->getId());
            }

            $template = $mail->getTemplate();

            // Unsubscriptions are disabled depending on whether the email template takes them into account or not.
            if ($template && $template->isUnsubscriptions()) {
                if (
                    in_array(
                        strtolower($recipient->getToEmail()),
                        $unsubscribedEmails
                    )
                ) {
                    $recipient->setStatus(Recipient::STATUS_UNSUBSCRIBED);
                    $this->updateMail($mail);

                    continue;
                }
            }

            if ($template && !$template->hasSendToErrors()) {
                if (in_array($recipient->getToEmail(), array_column($errorArray, 'email'))) {
                    $recipient->setStatus(Recipient::STATUS_ERROR);
                    $this->updateMail($mail);

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

    public function getUnsubscribedEmails(): array
    {
        $unsubscribedArray = $this->unsubscribeEmailRepository->findEmailsUnsubscribed();
        $emails = array_column($unsubscribedArray, 'email');

        return array_map('strtolower', $emails);
    }

    protected function send(Mail $mail, Recipient $recipient, bool $updateSendingDate = true): bool
    {
        try {
            if ($mail->getDsn()) {
                $transport = Transport::fromDsn($mail->getDsn());
                $mailer = new SymfonyMailer($transport);
            } else {
                $mailer = $this->mailer;
            }

            $mailer->send($this->mailBuilder->buildMail($mail, $recipient));
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

        $counts = $this->recipientRepository->countStatusesByMail($mail);

        $mail
            ->setTotalSended($counts['sent']['count'])
            ->setTotalUnsubscribed($counts['unsubscribed']['count'])
            ->setTotalError($counts['error']['count'])
            ->setTotalToSend($mail->getRecipients()->count());

        $totalRecipientsToSend = $mail->getTotalToSend() - $mail->getTotalUnsubscribed();
        $totalRecipientsSent = $mail->getTotalSended() + $mail->getTotalError();

        if ($totalRecipientsToSend === $mail->getTotalToSend()) {
            $mail->setStatus(Mail::STATUS_SENT);
        } elseif ($mail->getTotalError() === $totalRecipientsSent) {
            $mail->setStatus(Mail::STATUS_ERROR);
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
