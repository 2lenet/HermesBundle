<?php

namespace phpunit\Unit\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Entity\Recipient;
use Lle\HermesBundle\Entity\Template;
use Lle\HermesBundle\Enum\StatusEnum;
use Lle\HermesBundle\Repository\MailRepository;
use Lle\HermesBundle\Repository\RecipientRepository;
use Lle\HermesBundle\Repository\UnsubscribeEmailRepository;
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
    public function testSendAllMail(): void
    {
        $sender = new SenderService(
            $this->getMockMailer(),
            $this->getMockEntityManager(),
            $this->getMockRecipientRepository(),
            $this->getMockUnsubscribeEmail(),
            $this->getMockMailRepository(),
            $this->getMockParameterBag(),
            $this->getMockRouter(),
            $this->getEnvironment()
        );
        $sender->sendAllMail();
    }

    protected function getMockMailer(): MailerInterface
    {
        return $this->createMock(MailerInterface::class);
    }

    protected function getMockEntityManager(): EntityManagerInterface
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::exactly(2))
            ->method('persist')
            ->withConsecutive(
                [self::callback(function (Recipient $recipient) {
                    return StatusEnum::SENT === $recipient->getStatus();
                })],
                [self::callback(function (Mail $mail) {
                    return StatusEnum::SENT === $mail->getStatus()
                        && 1 === $mail->getTotalSended()
                        && 1 === $mail->getTotalUnsubscribed()
                        && 1 === $mail->getTotalError();
                })]
            );
        return $entityManager;
    }

    protected function getMockRecipientRepository(): RecipientRepository
    {
        $template = $this->getTemplate();
        $mail = $this->getMail($template);
        $recipient = $this->getRecipient($mail);

        $repo = $this->createMock(RecipientRepository::class);
        $repo->expects(self::exactly(1))->method('disableErrors');
        $repo->expects(self::exactly(1))
            ->method('findRecipientsSending')
            ->with(self::equalTo('ok'), self::equalTo('sending'), self::equalTo(10))
            ->will(self::returnValue([$recipient]));

        $repo->method('findBy')->willReturn([$recipient]);

        return $repo;
    }

    protected function getTemplate(): Template
    {
        $template = new Template();
        $template->setId(1);
        $template->setLibelle('template_libelle');
        $template->setSubject('template_subject');
        $template->setSenderEmail('no-reply@email.com');
        $template->setMjml('template_mjml');
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
        $mail->setMjml($template->getMjml());
        $mail->setHtml($template->getHtml());
        $mail->setText($template->getText());
        $mail->setTotalToSend(1);
        $mail->setStatus(StatusEnum::SENDING);
        return $mail;
    }

    protected function getRecipient(Mail $mail): Recipient
    {
        $recipient = new Recipient();
        $recipient->setId(1);
        $recipient->setMail($mail);
        $recipient->setToEmail('john.doe@test.com');
        return $recipient;
    }

    protected function getMockUnsubscribeEmail(): UnsubscribeEmailRepository
    {
        $repo = $this->createMock(UnsubscribeEmailRepository::class);
        $repo->expects(self::exactly(1))
            ->method('findEmailsUnsubscribed')
            ->willReturn(['unsubscribe@email.com']);
        return $repo;
    }

    protected function getMockMailRepository(): MailRepository
    {
        $repo = $this->getMockBuilder(MailRepository::class)
            ->disableOriginalConstructor()
            ->addMethods(['findByStatus'])
            ->getMock();
        $repo->expects(self::exactly(1))
            ->method('findByStatus')
            ->with(self::equalTo('sending'))
            ->willReturn([]);
        return $repo;
    }

    protected function getMockParameterBag(): ParameterBagInterface
    {
        $mock = $this->createMock(ParameterBagInterface::class);
        $mock->method('get')->will(self::returnCallback(function (string $name) {
            $value = $name;
            switch ($name) {
                case 'lle_hermes.bounce_email':
                    $value = 'no-reply@test.com';
                    break;
            }
            return $value;
        }));
        return $mock;
    }

    protected function getMockRouter(): RouterInterface
    {
        $context = new RequestContext();
        $mock = $this->createMock(RouterInterface::class);
        $mock->method('getContext')->will(self::returnValue($context));
        return $mock;
    }

    protected function getEnvironment(): Environment
    {
        return new Environment($this->getMockLoader());
    }

    protected function getMockLoader(): LoaderInterface
    {
        return $this->createMock(LoaderInterface::class);
    }
}
