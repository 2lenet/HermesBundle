<?php

namespace App\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Entity\Recipient;
use Lle\HermesBundle\Entity\Template;
use Lle\HermesBundle\Repository\EmailErrorRepository;
use Lle\HermesBundle\Repository\RecipientRepository;
use Lle\HermesBundle\Repository\UnsubscribeEmailRepository;
use Lle\HermesBundle\Service\ErrorLogger;
use Lle\HermesBundle\Service\MailBuilder;
use Lle\HermesBundle\Service\Sender;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Exception\RfcComplianceException;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\Loader\LoaderInterface;

/**
 * Class SenderTest
 * @package phpunit\Unit\Service
 *
 * @author 2LE <2le@2le.net>
 */
class SenderTest extends TestCase
{
    public function testSendAllMails(): void
    {
        $sender = new Sender(
            $this->getMockEntityManager(),
            $this->getMockEmailErrorRepository(),
            $this->getMockMailer(),
            $this->getMockMailBuilder(),
            $this->getMockParameterBag(),
            $this->getMockRecipientRepository(),
            $this->getMockUnsubscribeEmailRepository(),
            $this->getMockErrorLogger(),
        );
        self::assertEquals(1, $sender->sendAllMails());
    }

    public function testHandleSendFailureProgressivelyRetries(): void
    {
        $parameterBag = $this->createMock(ParameterBagInterface::class);
        $parameterBag->method('get')->willReturnMap([
            ['lle_hermes.recipient_max_retry', 3],
        ]);

        $sender = new Sender(
            $this->getMockEntityManager(),
            $this->getMockEmailErrorRepository(),
            $this->getMockMailer(),
            $this->getMockMailBuilder(),
            $parameterBag,
            $this->createMock(RecipientRepository::class),
            $this->createMock(UnsubscribeEmailRepository::class),
            $this->getMockErrorLogger(),
        );

        $reflection = new \ReflectionClass(Sender::class);
        $handleSendFailure = $reflection->getMethod('handleSendFailure');
        $handleSendFailure->setAccessible(true);

        $mail = new Mail();
        $recipient = new Recipient();
        $recipient->setMail($mail);
        $recipient->setToEmail('john.doe@test.com');
        $recipient->setStatus(Recipient::STATUS_SENDING);

        // 1st failure → RETRY, mail rescheduled +1 min
        $before = new \DateTime();
        $handleSendFailure->invoke($sender, $recipient);
        self::assertSame(Recipient::STATUS_RETRY, $recipient->getStatus());
        self::assertSame(1, $recipient->getRetryCount());
        $sendAtDate = $mail->getSendAtDate();
        self::assertNotNull($sendAtDate);
        $expected = (clone $before)->add(new \DateInterval('PT1M'));
        self::assertEqualsWithDelta($expected->getTimestamp(), $sendAtDate->getTimestamp(), 5);

        // 2nd failure → RETRY, mail rescheduled +1 hour
        $before = new \DateTime();
        $handleSendFailure->invoke($sender, $recipient);
        self::assertSame(Recipient::STATUS_RETRY, $recipient->getStatus());
        self::assertSame(2, $recipient->getRetryCount());
        $sendAtDate = $mail->getSendAtDate();
        self::assertNotNull($sendAtDate);
        $expected = (clone $before)->add(new \DateInterval('PT1H'));
        self::assertEqualsWithDelta($expected->getTimestamp(), $sendAtDate->getTimestamp(), 5);

        // 3rd failure → RETRY, mail rescheduled +1 day
        $before = new \DateTime();
        $handleSendFailure->invoke($sender, $recipient);
        self::assertSame(Recipient::STATUS_RETRY, $recipient->getStatus());
        self::assertSame(3, $recipient->getRetryCount());
        $sendAtDate = $mail->getSendAtDate();
        self::assertNotNull($sendAtDate);
        $expected = (clone $before)->add(new \DateInterval('P1D'));
        self::assertEqualsWithDelta($expected->getTimestamp(), $sendAtDate->getTimestamp(), 5);

        // 4th failure → ERROR
        $handleSendFailure->invoke($sender, $recipient);
        self::assertSame(Recipient::STATUS_ERROR, $recipient->getStatus());
        self::assertSame(3, $recipient->getRetryCount());
    }

    public function testGetUnsubscribedEmailsGroupsByEmail(): void
    {
        $repository = $this->createMock(UnsubscribeEmailRepository::class);
        $repository->method('findEmailsUnsubscribed')->willReturn([
            ['email' => 'Global@Email.com', 'typeTemplateId' => null],
            ['email' => 'typed@email.com', 'typeTemplateId' => 2],
            ['email' => 'typed@email.com', 'typeTemplateId' => '3'],
        ]);

        self::assertSame([
            'global@email.com' => ['global' => true, 'types' => []],
            'typed@email.com' => ['global' => false, 'types' => [2, 3]],
        ], $this->getSender($repository)->getUnsubscribedEmails());
    }

    public function testIsUnsubscribed(): void
    {
        $sender = $this->getSender($this->createMock(UnsubscribeEmailRepository::class));
        $unsubscribedEmails = [
            'global@email.com' => ['global' => true, 'types' => []],
            'typed@email.com' => ['global' => false, 'types' => [2]],
        ];

        // Global unsubscription blocks every template, typed or not
        self::assertTrue($sender->isUnsubscribed($unsubscribedEmails, 'GLOBAL@email.com', 2));
        self::assertTrue($sender->isUnsubscribed($unsubscribedEmails, 'global@email.com', null));
        // Typed unsubscription only blocks templates of that type
        self::assertTrue($sender->isUnsubscribed($unsubscribedEmails, 'typed@email.com', 2));
        self::assertFalse($sender->isUnsubscribed($unsubscribedEmails, 'typed@email.com', 3));
        self::assertFalse($sender->isUnsubscribed($unsubscribedEmails, 'typed@email.com', null));
        self::assertFalse($sender->isUnsubscribed($unsubscribedEmails, 'other@email.com', 2));
    }

    protected function getSender(UnsubscribeEmailRepository $unsubscribeEmailRepository): Sender
    {
        return new Sender(
            $this->getMockEntityManager(),
            $this->getMockEmailErrorRepository(),
            $this->getMockMailer(),
            $this->getMockMailBuilder(),
            $this->getMockParameterBag(),
            $this->createMock(RecipientRepository::class),
            $unsubscribeEmailRepository,
            $this->getMockErrorLogger(),
        );
    }

    public function testSendSetsErrorWithoutRetryOnInvalidAddress(): void
    {
        $mailBuilder = $this->createMock(MailBuilder::class);
        $mailBuilder->method('buildMail')
            ->willThrowException(new RfcComplianceException('Email "contactcastelhouse.fr" is invalid.'));

        $parameterBag = $this->createMock(ParameterBagInterface::class);
        $parameterBag->method('get')->willReturnMap([
            ['lle_hermes.recipient_max_retry', 3],
        ]);

        $sender = new Sender(
            $this->getMockEntityManager(),
            $this->getMockEmailErrorRepository(),
            $this->getMockMailer(),
            $mailBuilder,
            $parameterBag,
            $this->createMock(RecipientRepository::class),
            $this->createMock(UnsubscribeEmailRepository::class),
            $this->getMockErrorLogger(),
        );

        $mail = new Mail();
        $recipient = new Recipient();
        $recipient->setCcMail($mail);
        $recipient->setToEmail('contactcastelhouse.fr');
        $recipient->setStatus(Recipient::STATUS_SENDING);

        $send = (new \ReflectionClass(Sender::class))->getMethod('send');

        self::assertFalse($send->invoke($sender, $mail, $recipient));
        self::assertSame(Recipient::STATUS_ERROR, $recipient->getStatus());
        self::assertSame(0, $recipient->getRetryCount());
        self::assertNull($mail->getSendAtDate());
    }

    /**
     * @return array<string, array{string, string[], string, int, int}>
     */
    public static function sendAllRecipientsProvider(): array
    {
        return [
            'main sent, cc in error' => [
                Recipient::STATUS_SENT,
                [Recipient::STATUS_ERROR, Recipient::STATUS_ERROR],
                Mail::STATUS_PARTIALLY_SENT,
                1,
                2,
            ],
            'main in error, cc sent' => [
                Recipient::STATUS_ERROR,
                [Recipient::STATUS_SENT, Recipient::STATUS_SENT],
                Mail::STATUS_PARTIALLY_SENT,
                2,
                1,
            ],
            'all sent' => [Recipient::STATUS_SENT, [Recipient::STATUS_SENT], Mail::STATUS_SENT, 2, 0],
            'all in error' => [Recipient::STATUS_ERROR, [Recipient::STATUS_ERROR], Mail::STATUS_ERROR, 0, 2],
            'cc still retrying' => [Recipient::STATUS_SENT, [Recipient::STATUS_RETRY], Mail::STATUS_SENDING, 1, 0],
        ];
    }

    /**
     * Expected recipient statuses are obtained by making the sending fail for the recipients concerned
     *
     * @dataProvider sendAllRecipientsProvider
     * @param string[] $ccStatuses
     */
    public function testSendAllRecipientsTakesCcRecipientsIntoAccount(
        string $mainStatus,
        array $ccStatuses,
        string $expectedStatus,
        int $expectedSent,
        int $expectedError,
    ): void {
        $mail = new Mail();
        $mail->setStatus(Mail::STATUS_SENDING);
        $outcomes = ['to@email.com' => $mainStatus];
        $mail->addRecipient((new Recipient())->setToEmail('to@email.com')->setStatus(Recipient::STATUS_SENDING));
        foreach ($ccStatuses as $key => $ccStatus) {
            $outcomes['cc' . $key . '@email.com'] = $ccStatus;
            $mail->addCcRecipient(
                (new Recipient())->setToEmail('cc' . $key . '@email.com')->setStatus(Recipient::STATUS_SENDING)
            );
        }

        $sender = $this->getSenderWithOutcomes($outcomes);
        $sender->sendAllRecipients($mail->getAllRecipients()->toArray());

        foreach ($mail->getAllRecipients() as $recipient) {
            self::assertSame($outcomes[(string)$recipient->getToEmail()] ?? null, $recipient->getStatus());
        }
        self::assertSame($expectedStatus, $mail->getStatus());
        self::assertSame(1 + count($ccStatuses), $mail->getTotalToSend());
        self::assertSame($expectedSent, $mail->getTotalSended());
        self::assertSame($expectedError, $mail->getTotalError());
    }

    /**
     * Sender whose sending ends up with the given recipient status, indexed by email:
     * an invalid address gives an error, a transport error gives a retry.
     *
     * @param array<string, string> $outcomes
     */
    protected function getSenderWithOutcomes(array $outcomes): Sender
    {
        $mailBuilder = $this->createMock(MailBuilder::class);
        $mailBuilder->method('buildMail')->willReturnCallback(
            function (Mail $mail, Recipient $recipient) use ($outcomes): Email {
                return match ($outcomes[$recipient->getToEmail()] ?? Recipient::STATUS_SENT) {
                    Recipient::STATUS_ERROR => throw new RfcComplianceException('Invalid address'),
                    Recipient::STATUS_RETRY => throw new TransportException('Transport error'),
                    default => new Email(),
                };
            }
        );

        $parameterBag = $this->createMock(ParameterBagInterface::class);
        $parameterBag->method('get')->willReturnMap([
            ['lle_hermes.recipient_max_retry', 3],
            ['lle_hermes.recipient_error_retry', 3],
        ]);

        return new Sender(
            $this->getMockEntityManager(),
            $this->getMockEmailErrorRepository(),
            $this->getMockMailer(),
            $mailBuilder,
            $parameterBag,
            $this->createMock(RecipientRepository::class),
            $this->createMock(UnsubscribeEmailRepository::class),
            $this->getMockErrorLogger(),
        );
    }

    protected function getMockMailer(): MailerInterface
    {
        return $this->createMock(MailerInterface::class);
    }

    protected function getMockEmailErrorRepository(): EmailErrorRepository
    {
        $repository = $this->createMock(EmailErrorRepository::class);

        return $repository;
    }

    protected function getMockEntityManager(): EntityManagerInterface
    {
        return $this->createMock(EntityManagerInterface::class);
    }

    protected function getMockParameterBag(): ParameterBagInterface
    {
        return $this->createMock(ParameterBagInterface::class);
    }

    protected function getMockErrorLogger(): ErrorLogger
    {
        return $this->createMock(ErrorLogger::class);
    }

    protected function getMockRecipientRepository(): RecipientRepository
    {
        $template = $this->getTemplate();
        $mail = $this->getMail($template);
        $recipient = $this->getRecipient($mail);

        $repository = $this->createMock(RecipientRepository::class);
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
            ->willReturn([['email' => 'unsubscribe@email.com', 'typeTemplateId' => null]]);

        return $repository;
    }

    protected function getMockMailBuilder(): MailBuilder
    {
        return $this->createMock(MailBuilder::class);
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
        $mail->setSubject((string)$template->getSubject());
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
