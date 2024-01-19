<?php

namespace App\Tests\Entity;

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
        $unsubcribeEmail = new UnsubscribeEmail();

        $unsubcribeEmail->setId(1);
        self::assertEquals(1, $unsubcribeEmail->getId());

        $unsubcribeEmail->setEmail('test@test.net');
        self::assertEquals('test@test.net', $unsubcribeEmail->getEmail());

        $date = new DateTime();
        $unsubcribeEmail->setUnsubscribeDate($date);
        self::assertEquals($date, $unsubcribeEmail->getUnsubscribeDate());

        $validator = Validation::createValidatorBuilder()->getValidator();

        $errors = $validator->validate($unsubcribeEmail);
        self::assertCount(0, $errors);
    }
}
