<?php

namespace phpunit\Unit\Entity;

use DateTime;
use Lle\HermesBundle\Entity\EmailError;
use Lle\HermesBundle\Entity\Error;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

/**
 * Class ErrorTest
 * @package phpunit\Unit\Entity
 *
 * @author 2LE <2le@2le.net>
 */
class ErrorTest extends TestCase
{
    public function testCreate(): void
    {
        $entity = new Error();
        $entity->setId(1);
        self::assertEquals(1, $entity->getId());

        $date = new DateTime();
        self::assertEquals($date, $entity->setDate($date)->getDate());
        self::assertEquals('subject', $entity->setSubject('subject')->getSubject());
        self::assertEquals('content', $entity->setContent('content')->getContent());
        $emailError = new EmailError();
        self::assertEquals($emailError, $entity->setEmailError($emailError)->getEmailError());

        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();

        $errors = $validator->validate($entity);
        self::assertEquals(0, count($errors));
    }

    public function testInvalidAssert(): void
    {
        $entity = new Error();
        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();

        // Date
        $violations = $validator->validateProperty($entity, 'date');
        self::assertCount(1, $violations);
        self::assertEquals(
            'This value should not be blank.',
            $violations[0]->getMessage()
        );

        // Subject
        $violations = $validator->validateProperty($entity, 'subject');
        self::assertCount(1, $violations);
        self::assertEquals(
            'This value should not be blank.',
            $violations[0]->getMessage()
        );

        $entity->setSubject(str_repeat('a', 1025));
        $violations = $validator->validateProperty($entity, 'subject');
        self::assertCount(1, $violations);
        self::assertEquals(
            'This value is too long. It should have 1024 characters or less.',
            $violations[0]->getMessage()
        );

        // Content
        $violations = $validator->validateProperty($entity, 'content');
        self::assertCount(1, $violations);
        self::assertEquals(
            'This value should not be blank.',
            $violations[0]->getMessage()
        );

        // Email Error
        $violations = $validator->validateProperty($entity, 'emailError');
        self::assertCount(1, $violations);
        self::assertEquals(
            'This value should not be blank.',
            $violations[0]->getMessage()
        );
    }
}
