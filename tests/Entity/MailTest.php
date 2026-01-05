<?php

namespace App\Tests\Entity;

use DateTime;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Entity\Recipient;
use Lle\HermesBundle\Entity\Template;
use Lle\HermesBundle\Model\MailDto;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

/**
 * Class MailTest
 * @package phpunit\Entity
 *
 * @author 2LE <2le@2le.net>
 */
class MailTest extends TestCase
{
    public function testMailCreate(): void
    {
        $mail = new Mail();

        $mail->setId(1);
        self::assertEquals(1, $mail->getId());

        $template = new Template();
        $mail->setTemplate($template);
        self::assertEquals($template, $mail->getTemplate());

        $mail->setData(['hello']);
        self::assertEquals(['hello'], $mail->getData());

        $mail->setStatus(MailDto::DRAFT);
        self::assertEquals(MailDto::DRAFT, $mail->getStatus());

        self::assertEquals(0, $mail->getTotalToSend());
        $mail->setTotalToSend(2);
        self::assertEquals(2, $mail->getTotalToSend());

        self::assertEquals(0, $mail->getTotalSended());
        $mail->setTotalSended(2);
        self::assertEquals(2, $mail->getTotalSended());

        $recipient = new Recipient();
        $mail->addRecipient($recipient);
        $recipients = $mail->getRecipients();
        self::assertTrue($recipients->contains($recipient));
        self::assertEquals($mail, $recipients->first()->getMail());
        $mail->removeRecipient($recipient);
        self::assertCount(0, $mail->getRecipients());

        $mail->setSubject('subject');
        self::assertEquals('subject', $mail->getSubject());

        self::assertNull($mail->getText());
        $mail->setText('text');
        self::assertEquals('text', $mail->getText());

        self::assertNull($mail->getHtml());
        $mail->setHtml('html');
        self::assertEquals('html', $mail->getHtml());

        self::assertNull($mail->getCreatedAt());
        $createdAt = new DateTime();
        $mail->setCreatedAt($createdAt);
        self::assertEquals($createdAt, $mail->getCreatedAt());

        self::assertNull($mail->getUpdatedAt());
        $updatedAt = new DateTime();
        $mail->setUpdatedAt($updatedAt);
        self::assertEquals($updatedAt, $mail->getUpdatedAt());

        self::assertNull($mail->getSendingDate());
        $sendingDate = new DateTime();
        $mail->setSendingDate($sendingDate);
        self::assertEquals($sendingDate, $mail->getSendingDate());

        self::assertEquals(0, $mail->getTotalUnsubscribed());
        $mail->setTotalUnsubscribed(2);
        self::assertEquals(2, $mail->getTotalUnsubscribed());

        self::assertEquals(0, $mail->getTotalError());
        $mail->setTotalError(2);
        self::assertEquals(2, $mail->getTotalError());

        self::assertEquals([], $mail->getAttachement());
        $mail->setAttachement(['hello']);
        self::assertEquals(['hello'], $mail->getAttachement());

        self::assertEquals(0, $mail->getTotalOpened());
        $mail->setTotalOpened(2);
        self::assertEquals(2, $mail->getTotalOpened());

        $validator = Validation::createValidatorBuilder()->getValidator();

        $errors = $validator->validate($mail);
        self::assertCount(0, $errors);
    }

    public function testGetSendingRecipients(): void
    {
        $recipient1 = new Recipient();
        $recipient1->setStatus(Recipient::STATUS_SENDING);

        $recipient2 = new Recipient();
        $recipient2->setStatus(Recipient::STATUS_SENT);

        $recipient3 = new Recipient();
        $recipient3->setStatus(Recipient::STATUS_CANCELLED);

        $recipient4 = new Recipient();
        $recipient4->setStatus(Recipient::STATUS_ERROR);

        $recipient5 = new Recipient();
        $recipient5->setStatus(Recipient::STATUS_UNSUBSCRIBED);

        $mail = new Mail();
        $mail
            ->setStatus(Mail::STATUS_SENDING)
            ->addRecipient($recipient1)
            ->addRecipient($recipient2)
            ->addRecipient($recipient3)
            ->addRecipient($recipient4)
            ->addRecipient($recipient5);

        self::assertCount(1, $mail->getSendingRecipients());
    }
}
