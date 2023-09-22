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
        $error = new Error();

        $error->setId(1);
        self::assertEquals(1, $error->getId());

        $date = new DateTime();
        $error->setDate($date);
        self::assertEquals($date, $error->getDate());

        $error->setSubject('subject');
        $error->setContent('content');
        self::assertEquals('subject', $error->getSubject());
        self::assertEquals('content', $error->getContent());

        $emailError = new EmailError();
        $error->setEmailError($emailError);
        self::assertEquals($emailError, $error->getEmailError());

        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();

        $errors = $validator->validate($error);
        self::assertCount(0, $errors);
    }
}
