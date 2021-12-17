<?php

namespace phpunit\Unit\Entity;

use DateTime;
use Lle\HermesBundle\Entity\EmailError;
use Lle\HermesBundle\Entity\Error;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

/**
 * Class EmailErrorTest
 * @package phpunit\Unit\Entity
 *
 * @author 2LE <2le@2le.net>
 */
class EmailErrorTest extends TestCase
{
    public function testCreate(): void
    {
        $entity = new EmailError();
        $entity->setId(1);
        self::assertEquals(1, $entity->getId());
        self::assertEquals(1, $entity->getNbError());
        self::assertEquals(2, $entity->setNbError(2)->getNbError());
        $date = new DateTime();
        self::assertEquals($date, $entity->setDateError($date)->getDateError());
        self::assertEquals('john.doe@email.com', $entity->setEmail('john.doe@email.com')->getEmail());
        $error = new Error();
        self::assertCount(0, $entity->getErrors());
        $entity->addError($error);
        self::assertCount(1, $entity->getErrors());
        self::assertEquals($error, $entity->getErrors()->first());
        self::assertEquals($entity, $error->getEmailError());
        self::assertCount(0, $entity->removeErrror($error)->getErrors());

        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();

        $errors = $validator->validate($entity);
        self::assertEquals(0, count($errors));
    }

    public function testInvalidAssert(): void
    {
        $entity = new EmailError();
        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();

        // Date Error
        $violations = $validator->validateProperty($entity, 'dateError');
        self::assertCount(1, $violations);
        self::assertEquals(
            'This value should not be blank.',
            $violations[0]->getMessage()
        );

        // Email
        $violations = $validator->validateProperty($entity, 'email');
        self::assertCount(1, $violations);
        self::assertEquals(
            'This value should not be blank.',
            $violations[0]->getMessage()
        );

        $entity->setEmail(str_repeat('a', 256));
        $violations = $validator->validateProperty($entity, 'email');
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
