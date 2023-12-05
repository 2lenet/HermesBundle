<?php

namespace phpunit\Unit\Entity;

use DateTime;
use Lle\HermesBundle\Entity\Link;
use Lle\HermesBundle\Entity\LinkOpening;
use Lle\HermesBundle\Entity\Recipient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

/**
 * Class LinkOpeningTest
 * @package phpunit\Unit\Entity
 *
 * @author 2LE <2le@2le.net>
 */
class LinkOpeningTest extends TestCase
{
    public function testCreate(): void
    {
        $linkOpening = new LinkOpening();

        $linkOpening->setId(1);
        self::assertEquals(1, $linkOpening->getId());

        $link = new Link();
        $linkOpening->setLink($link);
        self::assertEquals($link, $linkOpening->getLink());

        $recipient = new Recipient();
        $linkOpening->setRecipient($recipient);
        self::assertEquals($recipient, $linkOpening->getRecipient());

        self::assertEquals(0, $linkOpening->getNbOpenings());
        $linkOpening->setNbOpenings(1);
        self::assertEquals(1, $linkOpening->getNbOpenings());

        self::assertNull($linkOpening->getCreatedAt());
        $createdAt = new DateTime();
        $linkOpening->setCreatedAt($createdAt);
        self::assertEquals($createdAt, $linkOpening->getCreatedAt());

        self::assertNull($linkOpening->getUpdatedAt());
        $updatedAt = new DateTime();
        $linkOpening->setUpdatedAt($updatedAt);
        self::assertEquals($updatedAt, $linkOpening->getUpdatedAt());

        $validator = Validation::createValidatorBuilder()->getValidator();
        $errors = $validator->validate($linkOpening);
        self::assertCount(0, $errors);
    }
}
