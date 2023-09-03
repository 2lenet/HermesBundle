<?php

namespace phpunit\Unit\Entity;

use DateTime;
use Lle\HermesBundle\Entity\UnsubscribeEmail;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

/**
 * Class UnsubscribeEmailTest
 * @package phpunit\Unit\Entity
 *
 * @author 2LE <2le@2le.net>
 */
class UnsubscribeEmailTest extends TestCase
{
    public function testUnsubscribeEmailCreate(): void
    {
        $entity = new UnsubscribeEmail();

        $entity->setId(1);
        self::assertEquals(1, $entity->getId());

        $entity->setEmail('test@test.net');
        self::assertEquals('test@test.net', $entity->getEmail());

        $date = new DateTime();
        $entity->setUnsubscribeDate($date);
        self::assertEquals($date, $entity->getUnsubscribeDate());

        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();

        $errors = $validator->validate($entity);
        self::assertEquals(0, count($errors));
    }

    public function testInvalidAssert(): void
    {
        $entity = new UnsubscribeEmail();
        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();

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

        $violations = $validator->validateProperty($entity, 'unsubscribeDate');
        self::assertCount(1, $violations);
        self::assertEquals(
            'This value should not be blank.',
            $violations[0]->getMessage()
        );
    }
}
