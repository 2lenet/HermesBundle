<?php

namespace phpunit\Unit\Entity;

use DateTime;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Entity\Recipient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

/**
 * Class RecipientTest
 * @package phpunit\Unit\Entity
 *
 * @author 2LE <2le@2le.net>
 */
class RecipientTest extends TestCase
{
    public function testCreate(): void
    {
        $recipient = new Recipient();

        $recipient->setId(1);
        self::assertEquals(1, $recipient->getId());

        $recipient->setToName('John Doe');
        self::assertEquals('John Doe', $recipient->getToName());

        $recipient->setToEmail('john.doe@test.com');
        self::assertEquals('john.doe@test.com', $recipient->getToEmail());

        $recipient->setData(['hello']);
        self::assertEquals(['hello'], $recipient->getData());

        $recipient->setStatus(Recipient::STATUS_SENDING);
        self::assertEquals(Recipient::STATUS_SENDING, $recipient->getStatus());

        self::assertEquals(0, $recipient->getNbRetry());
        $recipient->setNbRetry(2);
        self::assertEquals(2, $recipient->getNbRetry());

        $mail = new Mail();
        $recipient->setMail($mail);
        self::assertEquals($mail, $recipient->getMail());

        self::assertNull($recipient->getOpenDate());
        $date = new DateTime();
        $recipient->setOpenDate($date);
        self::assertEquals($date, $recipient->getOpenDate());

        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();

        $errors = $validator->validate($recipient);
        self::assertCount(0, $errors);
    }
}
