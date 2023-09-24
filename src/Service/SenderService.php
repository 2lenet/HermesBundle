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
 * Class SenderService
 * @package Lle\HermesBundle\Service
 *
 * @author 2LE <2le@2le.net>
 */
class SenderService
{
    private MailerInterface $mailer;
    private EntityManagerInterface $entityManager;
    private RecipientRepository $recipientRepository;
    private UnsubscribeEmailRepository $unsubscribeEmailRepository;
    private MailBuilderService $mailBuilderService;

    public function __construct(
        MailerInterface $mailer,
        EntityManagerInterface $entityManager,
        RecipientRepository $recipientRepository,
        UnsubscribeEmailRepository $unsubscribeEmailRepository,
        MailBuilderService $mailBuilderService
    ) {
        $this->mailer = $mailer;
        $this->entityManager = $entityManager;
        $this->recipientRepository = $recipientRepository;
        $this->unsubscribeEmailRepository = $unsubscribeEmailRepository;
        $this->mailBuilderService = $mailBuilderService;
    }

    public function sendAllMails(int $limit = 10): int
    {
        $this->recipientRepository->disableErrors();

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
                    $this->entityManager->flush();

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

    protected function send(Mail $mail, Recipient $recipient, bool $updateSendingDate = true): bool
    {
        try {
            $this->mailer->send($this->mailBuilderService->buildMail($mail, $recipient));
            $recipient->setStatus(Recipient::STATUS_SENT);

            if ($updateSendingDate) {
                $mail->setSendingDate(new DateTime());
            }

            $this->entityManager->flush();

            return true;
        } catch (TransportExceptionInterface $exception) {
            $recipient->setStatus(Recipient::STATUS_ERROR);
            $this->entityManager->flush();

            return false;
        } catch (RfcComplianceException $exception) {
            $recipient->setStatus(Recipient::STATUS_ERROR);
            $this->entityManager->flush();

            return false;
        } catch (Exception $exception) {
            $recipient->setStatus(Recipient::STATUS_ERROR);
            $mail->setStatus(Mail::STATUS_ERROR);
            $this->entityManager->flush();

            return false;
        }
    }

    protected function updateMail(Mail $mail): void
    {
        $destinataireSent = $this->recipientRepository
            ->findBy(['status' => Recipient::STATUS_SENT, 'mail' => $mail, 'test' => false]);
        $mail->setTotalSended(count($destinataireSent));

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

        $this->entityManager->flush();
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
