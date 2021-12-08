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
        $entity = new Recipient();

        $entity->setId(1);
        self::assertEquals(1, $entity->getId());

        $entity->setToName('John Doe');
        self::assertEquals('John Doe', $entity->getToName());

        $entity->setToEmail('john.doe@test.com');
        self::assertEquals('john.doe@test.com', $entity->getToEmail());

        $entity->setData(['hello']);
        self::assertEquals(['hello'], $entity->getData());

        $entity->setStatus('status');
        self::assertEquals('status', $entity->getStatus());

        $entity->setNbRetry(2);
        self::assertEquals('2', $entity->getNbRetry());

        $mail = new Mail();
        $entity->setMail($mail);
        self::assertEquals($mail, $entity->getMail());

        self::assertNull($entity->getOpenDate());
        $date = new DateTime();
        $entity->setOpenDate($date);
        self::assertEquals($date, $entity->getOpenDate());

        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();

        $errors = $validator->validate($entity);
        self::assertEquals(0, count($errors));
    }

    public function testInvalidAssert(): void
    {
        $entity = new Recipient();
        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();

        $entity->setToName(str_repeat('a', 256));
        $violations = $validator->validateProperty($entity, 'toName');
        self::assertCount(1, $violations);
        self::assertEquals(
            'This value is too long. It should have 255 characters or less.',
            $violations[0]->getMessage()
        );

        $violations = $validator->validateProperty($entity, 'toEmail');
        self::assertCount(1, $violations);
        self::assertEquals(
            'This value should not be blank.',
            $violations[0]->getMessage()
        );

        $entity->setToEmail(str_repeat('a', 256));
        $violations = $validator->validateProperty($entity, 'toEmail');
        self::assertCount(2, $violations);
        self::assertEquals(
            'This value is too long. It should have 255 characters or less.',
            $violations[0]->getMessage()
        );
        self::assertEquals(
            'This value is not a valid email address.',
            $violations[1]->getMessage()
        );
    }
}
