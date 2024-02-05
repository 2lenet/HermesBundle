<?php

namespace App\Tests\Entity;

use Lle\HermesBundle\Entity\Link;
use Lle\HermesBundle\Entity\LinkOpening;
use Lle\HermesBundle\Entity\Mail;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

/**
 * Class LinkTest
 * @package phpunit\Unit\Entity
 *
 * @author 2LE <2le@2le.net>
 */
class LinkTest extends TestCase
{
    public function testCreate(): void
    {
        $link = new Link();

        $link->setId(1);
        self::assertEquals(1, $link->getId());

        $link->setUrl('url');
        self::assertEquals('url', $link->getUrl());

        $mail = new Mail();
        $link->setMail($mail);
        self::assertEquals($mail, $link->getMail());

        $linkOpening = new LinkOpening();
        $link->addLinkOpening($linkOpening);
        $linkOpenings = $link->getLinkOpenings();
        self::assertTrue($linkOpenings->contains($linkOpening));
        self::assertEquals($link, $linkOpenings->first()->getLink());
        $link->removeLinkOpening($linkOpening);
        self::assertCount(0, $link->getLinkOpenings());

        $validator = Validation::createValidatorBuilder()->getValidator();
        $errors = $validator->validate($link);
        self::assertCount(0, $errors);
    }
}
