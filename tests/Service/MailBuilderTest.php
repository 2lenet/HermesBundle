<?php

namespace App\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\EntityFileBundle\Service\EntityFileLoader;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Entity\Recipient;
use Lle\HermesBundle\Entity\Template;
use Lle\HermesBundle\Service\MailBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mime\Address;
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
        );

        $email = $builder->buildMail($mail, $recipient);

        /** @var Address $returnPath */
        $returnPath = $email->getReturnPath();

        $this->assertEquals('bounce@2le.net', $returnPath->getAddress());
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
        );

        $email = $builder->buildMail($mail, $recipient);

        /** @var Address $replyTo */
        $replyTo = $email->getReplyTo()[0];

        $this->assertEquals('no-reply@2le.net', $replyTo->getAddress());
    }
}
