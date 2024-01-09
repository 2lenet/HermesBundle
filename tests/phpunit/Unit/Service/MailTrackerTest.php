<?php

namespace phpunit\Unit\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Entity\Recipient;
use Lle\HermesBundle\Repository\RecipientRepository;
use Lle\HermesBundle\Service\MailTracker;
use PHPUnit\Framework\TestCase;

/**
 * Class MailTrackerTest
 * @package phpunit\Unit\Service
 *
 * @author 2LE <2le@2le.net>
 */
class MailTrackerTest extends TestCase
{
    private Mail $mail;
    private MailTracker $mailTracker;
    private Recipient $recipient;

    public function setUp(): void
    {
        parent::setUp();

        $this->mail = $this->createMail();
        $this->recipient = $this->createRecipient($this->mail);

        $em = $this->createMock(EntityManagerInterface::class);
        $recipientRepository = $this->createMock(RecipientRepository::class);
        $recipientRepository->method('countOpenRecipients')->willReturn(1);
        $this->mailTracker = new MailTracker($em, $recipientRepository);
    }

    public function testUpdateTotalOpened(): void
    {
        self::assertEquals(0, $this->mail->getTotalOpened());

        $this->mailTracker->updateTotalOpened($this->recipient);

        self::assertEquals(1, $this->mail->getTotalOpened());
    }

    protected function createMail(): Mail
    {
        $mail = new Mail();
        $mail->setId(1);
        $mail->setStatus(Mail::STATUS_SENT);
        $mail->setSubject('Mail 1');
        $mail->setText('Content');
        $mail->setHtml('<p>Content</p>');

        return $mail;
    }

    protected function createRecipient(Mail $mail): Recipient
    {
        $recipient = new Recipient();
        $recipient->setToName('John Doe');
        $recipient->setToEmail('john.doe@test.com');
        $recipient->setStatus(Recipient::STATUS_SENT);
        $recipient->setMail($mail);

        return $recipient;
    }
}
