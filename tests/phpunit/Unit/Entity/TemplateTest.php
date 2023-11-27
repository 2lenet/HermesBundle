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
        $template = new Template();

        $template->setId(1);
        self::assertEquals(1, $template->getId());

        $template->setLibelle('libelle');
        self::assertEquals('libelle', $template->getLibelle());

        $template->setSubject('subject');
        self::assertEquals('subject', $template->getSubject());

        $template->setSenderName('John Doe');
        self::assertEquals('John Doe', $template->getSenderName());

        $template->setSenderEmail('john.doe@email.com');
        self::assertEquals('john.doe@email.com', $template->getSenderEmail());

        $template->setText('text');
        self::assertEquals('text', $template->getText());

        $template->setCode('code');
        self::assertEquals('code', $template->getCode());

        $template->setHtml('html');
        self::assertEquals('html', $template->getHtml());

        self::assertFalse($template->isUnsubscriptions());
        $template->setUnsubscriptions(true);
        self::assertTrue($template->isUnsubscriptions());

        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();

        $errors = $validator->validate($template);
        self::assertCount(0, $errors);
    }
}
