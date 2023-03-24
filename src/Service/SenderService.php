<?php

namespace Lle\HermesBundle\Service;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Entity\Recipient;
use Lle\HermesBundle\Exception\NoMailFoundException;
use Lle\HermesBundle\Repository\RecipientRepository;
use Lle\HermesBundle\Repository\UnsubscribeEmailRepository;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;

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

        $recipients = $this->recipientRepository->findRecipientsSending(Recipient::STATUS_SENDING, Mail::STATUS_SENDING, $limit);

        return $this->sendAllRecipients($recipients);
    }

    /**
     * @param Recipient[] $recipients
     */
    public function sendAllRecipients(array $recipients): int
    {
        $unsubscribedArray = $this->unsubscribeEmailRepository->findEmailsUnsubscribed();
        $nb = 0;

        foreach ($recipients as $recipient) {
            if (!$recipient->getMail() && !$recipient->getCcMail()) {
                throw new NoMailFoundException($recipient->getId());
            }

            $mail = $recipient->getMail() ?? $recipient->getCcMail();
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

    protected function send(Mail $mail, Recipient $recipient): bool
    {
        try {
            $this->mailer->send($this->mailBuilderService->buildMail($mail, $recipient));
            $recipient->setStatus(Recipient::STATUS_SENT);
            $this->entityManager->persist($recipient);

            $mail->setSendingDate(new DateTime());
            $this->entityManager->persist($mail);

            $this->entityManager->flush();

            return true;
        } catch (TransportException $transportException) {
            $recipient->setStatus(Recipient::STATUS_ERROR);
            $this->entityManager->flush();

            return false;
        }
    }

    protected function updateMail(Mail $mail): void
    {
        $destinataireSent = $this->recipientRepository
            ->findBy(['status' => Recipient::STATUS_SENT, 'mail' => $mail]);
        $mail->setTotalSended(count($destinataireSent));

        $unsubscribedMails = $this->recipientRepository
            ->findBy(['status' => Recipient::STATUS_UNSUBSCRIBED, 'mail' => $mail]);
        $mail->setTotalUnsubscribed(count($unsubscribedMails));

        $errorMails = $this->recipientRepository
            ->findBy(['status' => Recipient::STATUS_ERROR, 'mail' => $mail]);
        $mail->setTotalError(count($errorMails));

        $totalRecipientsToSend = $mail->getTotalToSend() - $mail->getTotalUnsubscribed();
        $totalRecipientsSended = $mail->getTotalSended() + $mail->getTotalError();

        if ($totalRecipientsSended === $totalRecipientsToSend) {
            $mail->setStatus(Mail::STATUS_SENT);
        }

        $this->entityManager->flush();
    }
}
