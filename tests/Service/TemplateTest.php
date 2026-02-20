<?php

namespace Service;

use Lle\HermesBundle\Entity\Template;
use Lle\HermesBundle\Entity\Translation\TemplateTranslation;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\Loader\LoaderInterface;

/**
 * Class MTemplateTest
 * @package phpunit\Unit\Service
 *
 * @author 2LE <2le@2le.net>
 */
class TemplateTest extends TestCase
{
    private Template $template;
    public function setUp(): void
    {
        parent::setUp();

        $this->template = $this->createTemplate();
    }

    public function testGetSubjectFromLocale(): void
    {
        self::assertEquals('test subject', $this->template->getSubjectFromLocale('en'));
        self::assertNotEquals('test subject', $this->template->getSubjectFromLocale('fr'));
    }

    public function testGetTextFromLocale(): void
    {
        self::assertEquals('test text', $this->template->getTextFromLocale('en'));
        self::assertNotEquals('test text', $this->template->getTextFromLocale('fr'));
    }

    public function testGetHtmlFromLocale(): void
    {
        self::assertEquals('<p>en html</p>', $this->template->getHtmlFromLocale('en'));
        self::assertNotEquals('<p>en html</p>', $this->template->getHtmlFromLocale('fr'));
    }

    public function testGetSenderNameFromLocale(): void
    {
        self::assertEquals('test senderName', $this->template->getSenderNameFromLocale('en'));
        self::assertNotEquals('test senderName', $this->template->getSenderNameFromLocale('fr'));
    }

    public function testGetSenderEmailFromLocale(): void
    {
        self::assertEquals('test senderEmail', $this->template->getSenderEmailFromLocale('en'));
        self::assertNotEquals('test senderEmail', $this->template->getSenderEmailFromLocale('fr'));
    }

    public function testGetMjmlFromLocale(): void
    {
        self::assertEquals('<mjml>test mjml</mjml>', $this->template->getMjmlFromLocale('en'));
        self::assertNotEquals('<mjml>test mjml</mjml>', $this->template->getMjmlFromLocale('fr'));
    }

    public function createTemplate(): Template
    {
        $template = new Template();
        $template->setSubject('subject');
        $template->setText('text');
        $template->setHtml('<p>html</p>');
        $template->setSenderName('senderName');
        $template->setSenderEmail('senderEmail');
        $template->setMjml('<mjml>mjml</mjml>');

        // translation
        $template->addTranslation(new TemplateTranslation('en', 'subject', 'test subject'));
        $template->addTranslation(new TemplateTranslation('en', 'text', 'test text'));
        $template->addTranslation(new TemplateTranslation('en', 'senderName', 'test senderName'));
        $template->addTranslation(new TemplateTranslation('en', 'html', '<p>en html</p>'));
        $template->addTranslation(new TemplateTranslation('en', 'senderEmail', 'test senderEmail'));
        $template->addTranslation(new TemplateTranslation('en', 'mjml', '<mjml>test mjml</mjml>'));

        return $template;
    }
}
