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
        $emailError = new EmailError();

        $emailError->setId(1);
        self::assertEquals(1, $emailError->getId());
        self::assertEquals(0, $emailError->getNbError());
        self::assertCount(0, $emailError->getErrors());

        $emailError->setNbError(2);
        self::assertEquals(2, $emailError->getNbError());

        $date = new DateTime();
        $emailError->setDateError($date);
        self::assertEquals($date, $emailError->getDateError());

        $emailError->setEmail('john.doe@email.com');
        self::assertEquals('john.doe@email.com', $emailError->getEmail());

        $error = new Error();
        $emailError->addError($error);
        self::assertCount(1, $emailError->getErrors());
        self::assertEquals($error, $emailError->getErrors()->first());
        self::assertEquals($emailError, $error->getEmailError());

        $emailError->removeError($error);
        self::assertCount(0, $emailError->getErrors());

        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();
        $errors = $validator->validate($emailError);
        self::assertCount(0, $errors);
    }
}
