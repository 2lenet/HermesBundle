<?php

namespace App\Tests\Service;

use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Entity\Template;
use Lle\HermesBundle\Service\MailTemplater;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\LoaderInterface;

/**
 * Class MailTemplaterTest
 * @package phpunit\Unit\Service
 *
 * @author 2LE <2le@2le.net>
 */
class MailTemplaterTest extends TestCase
{
    private MailTemplater $mailTemplater;

    public function setUp(): void
    {
        parent::setUp();

        $mail = $this->createMail();

        $loader = $this->createMock(LoaderInterface::class);
        $twig = new Environment($loader);
        $router = $this->createMock(RouterInterface::class);
        $this->mailTemplater = new MailTemplater($mail, $twig, $router);

        $this->mailTemplater->addData($mail->getData());
    }

    public function testGetSubject(): void
    {
        self::assertEquals('subject', $this->mailTemplater->getSubject());
    }

    public function testGetText(): void
    {
        self::assertEquals('text', $this->mailTemplater->getText());
    }

    public function testGetHtml(): void
    {
        self::assertEquals('<p>html</p>', $this->mailTemplater->getHtml());
    }

    public function testGetSenderName(): void
    {
        self::assertEquals('sender', $this->mailTemplater->getSenderName());
    }

    public function testRestoresStrictVariablesOfASharedEnvironment(): void
    {
        $twig = new Environment(new ArrayLoader(), ['strict_variables' => true, 'debug' => false]);
        $mail = $this->createMail();
        $mail->setSubject('[{{ unknown }}]');
        $router = $this->createMock(RouterInterface::class);
        $templater = new MailTemplater($mail, $twig, $router);

        $subject = $templater->getSubject();

        self::assertSame('[]', $subject);
        self::assertTrue($twig->isStrictVariables());
    }

    public function testLeavesNonStrictEnvironmentNonStrict(): void
    {
        $twig = new Environment(new ArrayLoader(), ['strict_variables' => false, 'debug' => true]);
        $mail = $this->createMail();
        $router = $this->createMock(RouterInterface::class);
        $templater = new MailTemplater($mail, $twig, $router);

        $templater->getSubject();

        self::assertFalse($twig->isStrictVariables());
    }

    protected function createMail(): Mail
    {
        $mail = new Mail();
        $mail->setId(1);
        $mail->setTemplate($this->createTemplate());
        $mail->setData([
            'subject' => 'subject',
            'text' => 'text',
            'html' => 'html',
            'sender' => 'sender',
        ]);
        $mail->setStatus(Mail::STATUS_DRAFT);
        $mail->setSubject('{{ subject }}');
        $mail->setText('{{ text }}');
        $mail->setHtml('<p>{{ html }}</p>');

        return $mail;
    }

    protected function createTemplate(): Template
    {
        $template = new Template();
        $template->setSenderName('{{ sender }}');

        return $template;
    }
}
