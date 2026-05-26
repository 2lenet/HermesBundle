<?php

namespace Lle\HermesBundle\Service;

use DateInterval;
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
        protected readonly ErrorLogger $errorLogger,
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
                throw new NoMailFoundException($recipient->getId() ?? 0);
            }

            $template = $mail->getTemplate();

            // Unsubscriptions are disabled depending on whether the email template takes them into account or not.
            if ($template && $template->isUnsubscriptions()) {
                if (
                    in_array(
                        strtolower((string)$recipient->getToEmail()),
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
                    $this->errorLogger->logError('Email was not sent to recipient in error', $recipient);
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
            $recipient->setRetryAt(null);

            if ($updateSendingDate) {
                $mail->setSendingDate(new DateTime());
            }

            return true;
        } catch (TransportExceptionInterface | RfcComplianceException $e) {
            $this->handleSendFailure($recipient);
            $this->errorLogger
                ->logError('Transport or RFC error with message : ' . $e->getMessage(), $recipient);

            return false;
        } catch (Exception $e) {
            $recipient->setStatus(Recipient::STATUS_ERROR);
            $this->errorLogger->logError('Generic error with message : ' . $e->getMessage(), $recipient);
            $mail->setStatus(Mail::STATUS_ERROR);

            return false;
        }
    }

    protected function updateMail(Mail $mail): void
    {
        $this->em->flush();

        $totalRetry = $mail->getRecipients()->filter(function (Recipient $recipient) {
            return $recipient->getStatus() === Recipient::STATUS_RETRY;
        })->count();

        $mail
            ->setTotalSended($mail->getRecipients()->filter(function (Recipient $recipient) {
                return $recipient->getStatus() === Recipient::STATUS_SENT;
            })->count())
            ->setTotalUnsubscribed($mail->getRecipients()->filter(function (Recipient $recipient) {
                return $recipient->getStatus() === Recipient::STATUS_UNSUBSCRIBED;
            })->count())
            ->setTotalError($mail->getRecipients()->filter(function (Recipient $recipient) {
                return $recipient->getStatus() === Recipient::STATUS_ERROR;
            })->count())
            ->setTotalToSend($mail->getRecipients()->count());

        $totalRecipientsToSend = $mail->getTotalToSend() - $mail->getTotalUnsubscribed();
        $totalRecipientsSent = $mail->getTotalSended() + $mail->getTotalError();

        if ($totalRetry === 0) {
            if ($mail->getTotalError() === $totalRecipientsToSend) {
                $mail->setStatus(Mail::STATUS_ERROR);
            } elseif ($totalRecipientsSent === $totalRecipientsToSend) {
                $mail->setStatus(Mail::STATUS_SENT);
            }
        }

        $this->em->flush();
    }

    protected function handleSendFailure(Recipient $recipient): void
    {
        /** @var int $maxRetry */
        $maxRetry = $this->parameters->get('lle_hermes.recipient_max_retry');
        $currentRetryCount = $recipient->getRetryCount();

        if ($currentRetryCount < $maxRetry) {
            $newRetryCount = $currentRetryCount + 1;
            $recipient
                ->setStatus(Recipient::STATUS_RETRY)
                ->setRetryCount($newRetryCount)
                ->setRetryAt((new DateTime())->add($this->computeRetryDelay($newRetryCount)));
        } else {
            $recipient->setStatus(Recipient::STATUS_ERROR);
            $recipient->setRetryAt(null);
        }
    }

    protected function computeRetryDelay(int $retryCount): DateInterval
    {
        return match (true) {
            $retryCount <= 1 => new DateInterval('PT1M'),
            $retryCount === 2 => new DateInterval('PT1H'),
            default => new DateInterval('P1D'),
        };
    }

    public function sendRecipient(Recipient $recipient): int
    {
        $mail = $recipient->getMail() ?? $recipient->getCcMail();
        if (!$mail) {
            throw new NoMailFoundException($recipient->getId() ?? 0);
        }

        if ($this->send($mail, $recipient, false)) {
            return 1;
        } else {
            print("error sending to " . $recipient);
        }

        return 0;
    }
}
