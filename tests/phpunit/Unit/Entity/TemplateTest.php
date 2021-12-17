<?php

namespace phpunit\Unit\Entity;

use Lle\HermesBundle\Entity\Template;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

/**
 * Class TemplateTest
 * @package phpunit\Unit\Entity
 *
 * @author 2LE <2le@2le.net>
 */
class TemplateTest extends TestCase
{
    public function testCreate(): void
    {
        $entity = new Template();

        $entity->setId(1);
        self::assertEquals(1, $entity->getId());

        $entity->setLibelle('libelle');
        self::assertEquals('libelle', $entity->getLibelle());

        $entity->setSubject('subject');
        self::assertEquals('subject', $entity->getSubject());

        $entity->setSenderName('John Doe');
        self::assertEquals('John Doe', $entity->getSenderName());

        $entity->setSenderEmail('john.doe@email.com');
        self::assertEquals('john.doe@email.com', $entity->getSenderEmail());

        $entity->setMjml('mjml');
        self::assertEquals('mjml', $entity->getMjml());

        $entity->setText('text');
        self::assertEquals('text', $entity->getText());

        $entity->setCode('code');
        self::assertEquals('code', $entity->getCode());

        $entity->setHtml('html');
        self::assertEquals('html', $entity->getHtml());

        self::assertTrue($entity->isUnsubscriptions());
        $entity->setUnsubscriptions(false);
        self::assertFalse($entity->isUnsubscriptions());

        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();

        $errors = $validator->validate($entity);
        self::assertEquals(0, count($errors));
    }

    public function testInvalidAssert(): void
    {
        $entity = new Template();
        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();

        // Libelle
        $violations = $validator->validateProperty($entity, 'libelle');
        self::assertCount(1, $violations);
        self::assertEquals(
            'This value should not be blank.',
            $violations[0]->getMessage()
        );

        $entity->setLibelle(str_repeat('a', 256));
        $violations = $validator->validateProperty($entity, 'libelle');
        self::assertCount(1, $violations);
        self::assertEquals(
            'This value is too long. It should have 255 characters or less.',
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

        // Sender Name
        $entity->setSenderName(str_repeat('a', 256));
        $violations = $validator->validateProperty($entity, 'senderName');
        self::assertCount(1, $violations);
        self::assertEquals(
            'This value is too long. It should have 255 characters or less.',
            $violations[0]->getMessage()
        );

        // Sender Email
        $violations = $validator->validateProperty($entity, 'senderEmail');
        self::assertCount(1, $violations);
        self::assertEquals(
            'This value should not be blank.',
            $violations[0]->getMessage()
        );

        $entity->setSenderEmail(str_repeat('a', 256));
        $violations = $validator->validateProperty($entity, 'senderEmail');
        self::assertCount(2, $violations);
        self::assertEquals(
            'This value is too long. It should have 255 characters or less.',
            $violations[0]->getMessage()
        );
        self::assertEquals(
            'This value is not a valid email address.',
            $violations[1]->getMessage()
        );

        // Code
        $violations = $validator->validateProperty($entity, 'code');
        self::assertCount(1, $violations);
        self::assertEquals(
            'This value should not be blank.',
            $violations[0]->getMessage()
        );

        $entity->setCode(str_repeat('a', 256));
        $violations = $validator->validateProperty($entity, 'code');
        self::assertCount(1, $violations);
        self::assertEquals(
            'This value is too long. It should have 255 characters or less.',
            $violations[0]->getMessage()
        );
    }
}
