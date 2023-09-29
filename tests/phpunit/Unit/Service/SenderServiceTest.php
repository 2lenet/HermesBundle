<?php

namespace phpunit\Unit\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Entity\Recipient;
use Lle\HermesBundle\Entity\Template;
use Lle\HermesBundle\Repository\RecipientRepository;
use Lle\HermesBundle\Repository\UnsubscribeEmailRepository;
use Lle\HermesBundle\Service\MailBuilderService;
use Lle\HermesBundle\Service\SenderService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\Loader\LoaderInterface;

/**
 * Class SenderServiceTest
 * @package phpunit\Unit\Service
 *
 * @author 2LE <2le@2le.net>
 */
class SenderServiceTest extends TestCase
{
    public function testSendAllMails(): void
    {
        $sender = new SenderService(
            $this->getMockEntityManager(),
            $this->getMockMailer(),
            $this->getMockMailBuilderService(),
            $this->getMockRecipientRepository(),
            $this->getMockUnsubscribeEmailRepository(),
        );
        self::assertEquals(1, $sender->sendAllMails());
    }

    protected function getMockMailer(): MailerInterface
    {
        return $this->createMock(MailerInterface::class);
    }

    protected function getMockEntityManager(): EntityManagerInterface
    {
        return $this->createMock(EntityManagerInterface::class);
    }

    protected function getMockRecipientRepository(): RecipientRepository
    {
        $template = $this->getTemplate();
        $mail = $this->getMail($template);
        $recipient = $this->getRecipient($mail);

        $repository = $this->createMock(RecipientRepository::class);
        $repository->expects(self::exactly(1))->method('disableErrors');
        $repository->expects(self::exactly(1))
            ->method('findRecipientsSending')
            ->with(self::equalTo(Recipient::STATUS_SENDING), self::equalTo(Mail::STATUS_SENDING), self::equalTo(10))
            ->willReturn([$recipient]);

        $repository->method('findBy')->willReturn([$recipient]);

        return $repository;
    }

    protected function getMockUnsubscribeEmailRepository(): UnsubscribeEmailRepository
    {
        $repository = $this->createMock(UnsubscribeEmailRepository::class);
        $repository->expects(self::exactly(1))
            ->method('findEmailsUnsubscribed')
            ->willReturn(['unsubscribe@email.com']);

        return $repository;
    }

    protected function getMockMailBuilderService(): MailBuilderService
    {
        return $this->createMock(MailBuilderService::class);
    }

    protected function getTemplate(): Template
    {
        $template = new Template();
        $template->setId(1);
        $template->setLibelle('template_libelle');
        $template->setSubject('template_subject');
        $template->setSenderEmail('no-reply@email.com');
        $template->setText('template_text');
        $template->setCode('template_code');
        $template->setHtml('template_html');

        return $template;
    }

    protected function getMail(Template $template): Mail
    {
        $mail = new Mail();
        $mail->setId(1);
        $mail->setTemplate($template);
        $mail->setSubject($template->getSubject());
        $mail->setHtml($template->getHtml());
        $mail->setText($template->getText());
        $mail->setTotalToSend(1);
        $mail->setStatus(Mail::STATUS_SENDING);

        return $mail;
    }

    protected function getRecipient(Mail $mail): Recipient
    {
        $recipient = new Recipient();
        $recipient->setId(1);
        $recipient->setMail($mail);
        $recipient->setToEmail('john.doe@test.com');
        $recipient->setStatus(Recipient::STATUS_SENDING);

        return $recipient;
    }
}
