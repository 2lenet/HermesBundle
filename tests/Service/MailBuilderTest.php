<?php

namespace App\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\EntityFileBundle\Service\EntityFileLoader;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Entity\Recipient;
use Lle\HermesBundle\Entity\Template;
use Lle\HermesBundle\Entity\TypeTemplate;
use Lle\HermesBundle\Service\ConsentManager;
use Lle\HermesBundle\Service\ConsentTokenManager;
use Lle\HermesBundle\Service\MailBuilder;
use Lle\HermesBundle\Service\UnsubscribeManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\HtmlToTextConverter\HtmlToTextConverterInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\Loader\LoaderInterface;

class MailBuilderTest extends TestCase
{
    public function testCustomBounceEmail(): void
    {
        $template = new Template();
        $template
            ->setLibelle('mail')
            ->setSubject('mail')
            ->setCode('MAIL')
            ->setSenderName('mail')
            ->setSenderEmail('mail@2le.net')
            ->setText('hi')
            ->setHtml('hi in but in html')
            ->setCustomBounceEmail('bounce@2le.net');

        $recipient = new Recipient();
        $recipient
            ->setId(69)
            ->setToName('recipient')
            ->setToEmail('recipient@2le.net')
            ->setStatus(Recipient::STATUS_SENDING);

        $mail = new Mail();
        $mail
            ->setId(69)
            ->setTemplate($template)
            ->setSubject('')
            ->setStatus(Mail::STATUS_SENDING);
        $recipient->setMail($mail);

        $bag = $this->createMock(ParameterBagInterface::class);
        $bag->method('get')->willReturn('parameter@2le.net');
        $loader = $this->createMock(LoaderInterface::class);
        $twig = new Environment($loader);

        $builder = new MailBuilder(
            $this->createMock(EntityManagerInterface::class),
            $bag,
            $this->createMock(RouterInterface::class),
            $twig,
            $this->createMock(EntityFileLoader::class),
            $this->createMock(ConsentManager::class),
            $this->createMock(ConsentTokenManager::class),
            $this->createMock(UnsubscribeManager::class),
        );

        $email = $builder->buildMail($mail, $recipient);

        /** @var Address $returnPath */
        $returnPath = $email->getReturnPath();

        $this->assertEquals('bounce@2le.net', $returnPath->getAddress());
    }

    public function testUnsubscribeLinkContainsTemplateType(): void
    {
        $typeTemplate = new TypeTemplate();
        $typeTemplate->setCode('COMMERCIAL')->setLabel('Commercial');

        $template = new Template();
        $template
            ->setLibelle('mail')
            ->setSubject('mail')
            ->setCode('MAIL')
            ->setSenderEmail('mail@2le.net')
            ->setText('hi')
            ->setHtml('hi in html')
            ->setUnsubscriptions(true)
            ->setTypeTemplate($typeTemplate);

        $recipient = new Recipient();
        $recipient
            ->setId(1)
            ->setToEmail('recipient@2le.net')
            ->setStatus(Recipient::STATUS_SENDING);

        $mail = new Mail();
        $mail
            ->setId(1)
            ->setTemplate($template)
            ->setSubject('')
            ->setStatus(Mail::STATUS_SENDING);
        $recipient->setMail($mail);

        $bag = $this->createMock(ParameterBagInterface::class);
        $bag->method('get')->willReturn('parameter@2le.net');

        $router = $this->createMock(RouterInterface::class);
        $router->expects(self::once())
            ->method('generate')
            ->with('unsubscribe', self::callback(function (array $parameters): bool {
                return $parameters['email'] === 'recipient@2le.net'
                    && $parameters['type'] === 'COMMERCIAL'
                    && $parameters['token'] === 'signed-token';
            }))
            ->willReturn('https://domain/hermes/unsubscribe');

        $unsubscribeManager = $this->createMock(UnsubscribeManager::class);
        $unsubscribeManager->expects(self::once())
            ->method('getToken')
            ->with('recipient@2le.net', $typeTemplate)
            ->willReturn('signed-token');

        $builder = new MailBuilder(
            $this->createMock(EntityManagerInterface::class),
            $bag,
            $router,
            new Environment($this->createMock(LoaderInterface::class)),
            $this->createMock(EntityFileLoader::class),
            $this->createMock(ConsentManager::class),
            $this->createMock(ConsentTokenManager::class),
            $unsubscribeManager,
        );

        $builder->buildMail($mail, $recipient);
    }

    public function testReplyToUsesMailSenderWhenSet(): void
    {
        $template = new Template();
        $template
            ->setLibelle('mail')
            ->setSubject('mail')
            ->setCode('MAIL')
            ->setSenderName('No Reply')
            ->setSenderEmail('no-reply@2le.net')
            ->setText('hi')
            ->setHtml('hi in but in html');

        $recipient = new Recipient();
        $recipient
            ->setId(69)
            ->setToName('recipient')
            ->setToEmail('recipient@2le.net')
            ->setStatus(Recipient::STATUS_SENDING);

        $mail = new Mail();
        $mail
            ->setId(69)
            ->setTemplate($template)
            ->setSubject('')
            ->setSenderName('Visiteur')
            ->setSenderEmail('visiteur@2le.net')
            ->setStatus(Mail::STATUS_SENDING);
        $recipient->setMail($mail);

        $bag = $this->createMock(ParameterBagInterface::class);
        $bag->method('get')->willReturn('parameter@2le.net');
        $loader = $this->createMock(LoaderInterface::class);
        $twig = new Environment($loader);

        $builder = new MailBuilder(
            $this->createMock(EntityManagerInterface::class),
            $bag,
            $this->createMock(RouterInterface::class),
            $twig,
            $this->createMock(EntityFileLoader::class),
            $this->createMock(ConsentManager::class),
            $this->createMock(ConsentTokenManager::class),
            $this->createMock(UnsubscribeManager::class),
        );

        $email = $builder->buildMail($mail, $recipient);

        /** @var Address $from */
        $from = $email->getFrom()[0];
        /** @var Address $replyTo */
        $replyTo = $email->getReplyTo()[0];

        $this->assertEquals('no-reply@2le.net', $from->getAddress());
        $this->assertEquals('visiteur@2le.net', $replyTo->getAddress());
        $this->assertEquals('Visiteur', $replyTo->getName());
    }

    public function testReplyToFallsBackToTemplateWhenMailSenderNotSet(): void
    {
        $template = new Template();
        $template
            ->setLibelle('mail')
            ->setSubject('mail')
            ->setCode('MAIL')
            ->setSenderName('No Reply')
            ->setSenderEmail('no-reply@2le.net')
            ->setText('hi')
            ->setHtml('hi in but in html');

        $recipient = new Recipient();
        $recipient
            ->setId(69)
            ->setToName('recipient')
            ->setToEmail('recipient@2le.net')
            ->setStatus(Recipient::STATUS_SENDING);

        $mail = new Mail();
        $mail
            ->setId(69)
            ->setTemplate($template)
            ->setSubject('')
            ->setStatus(Mail::STATUS_SENDING);
        $recipient->setMail($mail);

        $bag = $this->createMock(ParameterBagInterface::class);
        $bag->method('get')->willReturn('parameter@2le.net');
        $loader = $this->createMock(LoaderInterface::class);
        $twig = new Environment($loader);

        $builder = new MailBuilder(
            $this->createMock(EntityManagerInterface::class),
            $bag,
            $this->createMock(RouterInterface::class),
            $twig,
            $this->createMock(EntityFileLoader::class),
            $this->createMock(ConsentManager::class),
            $this->createMock(ConsentTokenManager::class),
            $this->createMock(UnsubscribeManager::class),
        );

        $email = $builder->buildMail($mail, $recipient);

        /** @var Address $replyTo */
        $replyTo = $email->getReplyTo()[0];

        $this->assertEquals('no-reply@2le.net', $replyTo->getAddress());
    }

    public function testTextIsGeneratedFromRenderedHtml(): void
    {
        $converter = $this->createMock(HtmlToTextConverterInterface::class);
        $converter->expects(self::once())
            ->method('convert')
            ->with('<p>Bonjour Somia, <a href="https://2le.net">voir</a></p>', 'utf-8')
            ->willReturn('Bonjour Somia, [voir](https://2le.net)');

        $email = $this->buildMailWithContent(
            'Old text',
            '<p>Bonjour {{ name }}, <a href="https://2le.net">voir</a></p>',
            $converter,
        );

        $this->assertEquals('Bonjour Somia, [voir](https://2le.net)', $email->getTextBody());
    }

    public function testTextIsKeptWithoutHtml(): void
    {
        $converter = $this->createMock(HtmlToTextConverterInterface::class);
        $converter->expects(self::never())->method('convert');

        $email = $this->buildMailWithContent('Hello {{ name }}', '', $converter);

        $this->assertEquals('Hello Somia', $email->getTextBody());
    }

    public function testDefaultConverterGeneratesText(): void
    {
        $email = $this->buildMailWithContent(
            '',
            '<html><head><style>p {}</style></head><body><p>Bonjour {{ name }}</p></body></html>',
        );

        $this->assertStringContainsString('Bonjour Somia', (string)$email->getTextBody());
        $this->assertStringNotContainsString('<p>', (string)$email->getTextBody());
        $this->assertStringNotContainsString('p {}', (string)$email->getTextBody());
    }

    private function buildMailWithContent(
        string $text,
        string $html,
        ?HtmlToTextConverterInterface $converter = null,
    ): Email {
        $template = new Template();
        $template
            ->setLibelle('mail')
            ->setSubject('mail')
            ->setCode('MAIL')
            ->setSenderEmail('mail@2le.net');

        $recipient = new Recipient();
        $recipient
            ->setId(1)
            ->setToEmail('recipient@2le.net')
            ->setStatus(Recipient::STATUS_SENDING);

        $mail = new Mail();
        $mail
            ->setId(1)
            ->setTemplate($template)
            ->setSubject('')
            ->setText($text)
            ->setHtml($html)
            ->setData(['name' => 'Somia'])
            ->setStatus(Mail::STATUS_SENDING);
        $recipient->setMail($mail);

        $bag = $this->createMock(ParameterBagInterface::class);
        $bag->method('get')->willReturn('parameter@2le.net');

        $builder = new MailBuilder(
            $this->createMock(EntityManagerInterface::class),
            $bag,
            $this->createMock(RouterInterface::class),
            new Environment($this->createMock(LoaderInterface::class)),
            $this->createMock(EntityFileLoader::class),
            $this->createMock(ConsentManager::class),
            $this->createMock(ConsentTokenManager::class),
            $this->createMock(UnsubscribeManager::class),
            $converter,
        );

        return $builder->buildMail($mail, $recipient);
    }
}
