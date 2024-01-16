<?php

namespace Lle\HermesBundle\Service;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Entity\Recipient;
use Lle\HermesBundle\Repository\RecipientRepository;

/**
 * Class MailTracker
 * @package Lle\HermesBundle\Service
 *
 * @author 2LE <2le@2le.net>
 */
class MailTracker
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly RecipientRepository $recipientRepository,
    ) {
    }

    public function updateTotalOpened(Recipient $recipient): int
    {
        $recipient->setOpenDate(new DateTime());
        $this->em->flush();

        /** @var Mail $mail */
        $mail = $recipient->getMail();
        $recipientsOpen = $this->recipientRepository->countOpenRecipients($mail);

        $mail->setTotalOpened($recipientsOpen);
        $this->em->flush();

        return $mail->getTotalOpened();
    }
}
