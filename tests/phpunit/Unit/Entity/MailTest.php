<?php

namespace phpunit\Unit\Entity;

use DateTime;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Entity\Recipient;
use Lle\HermesBundle\Entity\Template;
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

        $mail->setStatus('status');
        self::assertEquals('status', $mail->getStatus());

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

        self::assertNull($mail->getMjml());
        $mail->setMjml('mjml');
        self::assertEquals('mjml', $mail->getMjml());

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

        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();

        $errors = $validator->validate($mail);
        self::assertEquals(0, count($errors));
    }

    public function testInvalidAssert(): void
    {
        $mail = new Mail();
        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();
        $violations = $validator->validateProperty($mail, 'template');
        self::assertCount(1, $violations);
        self::assertEquals('This value should not be blank.', $violations[0]->getMessage());

        $violations = $validator->validateProperty($mail, 'status');
        self::assertCount(1, $violations);
        self::assertEquals('This value should not be blank.', $violations[0]->getMessage());

        $mail->setStatus(str_repeat('a', 256));
        $violations = $validator->validateProperty($mail, 'status');
        self::assertCount(1, $violations);
        self::assertEquals(
            'This value is too long. It should have 255 characters or less.',
            $violations[0]->getMessage()
        );

        $violations = $validator->validateProperty($mail, 'subject');
        self::assertCount(1, $violations);
        self::assertEquals('This value should not be blank.', $violations[0]->getMessage());

        $mail->setSubject(str_repeat('a', 1025));
        $violations = $validator->validateProperty($mail, 'subject');
        self::assertCount(1, $violations);
        self::assertEquals(
            'This value is too long. It should have 1024 characters or less.',
            $violations[0]->getMessage()
        );
    }
}
