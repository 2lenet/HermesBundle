<?php

namespace App\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Entity\Recipient;
use Lle\HermesBundle\Repository\EmailErrorRepository;
use Lle\HermesBundle\Repository\RecipientRepository;
use Lle\HermesBundle\Repository\UnsubscribeEmailRepository;
use Lle\HermesBundle\Service\ErrorLogger;
use Lle\HermesBundle\Service\Factory\RecipientFactory;
use Lle\HermesBundle\Service\MailBuilder;
use Lle\HermesBundle\Service\MailResender;
use Lle\HermesBundle\Service\Sender;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;

class MailResenderTest extends TestCase
{
    /** @var Recipient[] */
    protected array $persisted = [];

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function resendProvider(): array
    {
        return [
            'partially sent' => [Mail::STATUS_PARTIALLY_SENT, Recipient::STATUS_SENT, Recipient::STATUS_ERROR],
            'all in error' => [Mail::STATUS_ERROR, Recipient::STATUS_ERROR, Recipient::STATUS_ERROR],
        ];
    }

    /**
     * @dataProvider resendProvider
     */
    public function testResendThenSendSuccessfullyEndsSent(
        string $mailStatus,
        string $mainStatus,
        string $ccStatus,
    ): void {
        $mail = new Mail();
        $mail->setStatus($mailStatus);
        $main = (new Recipient())->setToEmail('to@email.com')->setStatus($mainStatus);
        $cc = (new Recipient())->setToEmail('cc@email.com')->setStatus($ccStatus);
        $mail->addRecipient($main);
        $mail->addCcRecipient($cc);

        $resender = $this->getMailResender();
        $nbInError = count(array_filter([$mainStatus, $ccStatus], fn($s) => $s === Recipient::STATUS_ERROR));
        self::assertSame($nbInError, $resender->resend($mail));
        self::assertSame(Mail::STATUS_SENDING, $mail->getStatus());
        self::assertCount($nbInError, $this->persisted);
        self::assertSame(Recipient::STATUS_RESENT, $cc->getStatus());

        // Doctrine would load the copies in the mail collections
        foreach ($this->persisted as $copy) {
            $copy->getCcMail() ? $mail->addCcRecipient($copy) : $mail->addRecipient($copy);
        }

        $this->getSender()->sendAllRecipients($this->persisted);

        self::assertSame(Mail::STATUS_SENT, $mail->getStatus());
        self::assertFalse($mail->canBeResent());
        self::assertSame(2, $mail->getTotalToSend());
        self::assertSame(2, $mail->getTotalSended());
        self::assertSame(0, $mail->getTotalError());

        // A second resend does not send the mail again
        $this->persisted = [];
        self::assertSame(0, $resender->resend($mail));
        self::assertSame([], $this->persisted);
    }

    public function testForceResendOfSentRecipientIsNotCountedTwice(): void
    {
        $mail = new Mail();
        $mail->setStatus(Mail::STATUS_SENT);
        $main = (new Recipient())->setToEmail('to@email.com')->setStatus(Recipient::STATUS_SENT);
        $mail->addRecipient($main);
        $mail->addCcRecipient((new Recipient())->setToEmail('cc@email.com')->setStatus(Recipient::STATUS_SENT));

        self::assertTrue($this->getMailResender()->forceResend($main));

        self::assertSame(Recipient::STATUS_RESENT, $main->getStatus());
        self::assertSame(Mail::STATUS_SENDING, $mail->getStatus());
        self::assertCount(1, $this->persisted);
        $copy = $this->persisted[0];
        self::assertSame(Recipient::STATUS_SENDING, $copy->getStatus());
        self::assertSame('to@email.com', $copy->getToEmail());

        $mail->addRecipient($copy);
        $this->getSender()->sendAllRecipients([$copy]);

        self::assertSame(Mail::STATUS_SENT, $mail->getStatus());
        self::assertSame(2, $mail->getTotalToSend());
        self::assertSame(2, $mail->getTotalSended());
    }

    /**
     * @return array<string, array{string}>
     */
    public static function pendingStatusProvider(): array
    {
        return [
            'sending' => [Recipient::STATUS_SENDING],
            'retry' => [Recipient::STATUS_RETRY],
        ];
    }

    /**
     * The original is no longer picked up by the sending, only the copy is
     *
     * @dataProvider pendingStatusProvider
     */
    public function testForceResendOfPendingRecipientIsNotSentTwice(string $status): void
    {
        $mail = new Mail();
        $mail->setStatus(Mail::STATUS_SENDING);
        $recipient = (new Recipient())->setToEmail('to@email.com')->setStatus($status);
        $mail->addRecipient($recipient);

        self::assertTrue($this->getMailResender()->forceResend($recipient));

        self::assertSame(Recipient::STATUS_RESENT, $recipient->getStatus());
        self::assertCount(1, $this->persisted);
        self::assertSame(Recipient::STATUS_SENDING, $this->persisted[0]->getStatus());
    }

    /**
     * @return array<string, array{string}>
     */
    public static function notResendableProvider(): array
    {
        return [
            'sent' => [Mail::STATUS_SENT],
            'sending' => [Mail::STATUS_SENDING],
        ];
    }

    /**
     * @dataProvider notResendableProvider
     */
    public function testResendIsIgnoredWhenMailCannotBeResent(string $mailStatus): void
    {
        $mail = new Mail();
        $mail->setStatus($mailStatus);
        $recipient = (new Recipient())->setToEmail('to@email.com')->setStatus(Recipient::STATUS_ERROR);
        $mail->addRecipient($recipient);

        self::assertSame(0, $this->getMailResender()->resend($mail));
        self::assertSame($mailStatus, $mail->getStatus());
        self::assertSame(Recipient::STATUS_ERROR, $recipient->getStatus());
        self::assertSame([], $this->persisted);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function draftOrCancelledProvider(): array
    {
        return [
            'draft' => [Mail::STATUS_DRAFT],
            'cancelled' => [Mail::STATUS_CANCELLED],
        ];
    }

    /**
     * @dataProvider draftOrCancelledProvider
     */
    public function testForceResendIsRefusedForDraftOrCancelledMail(string $mailStatus): void
    {
        $mail = new Mail();
        $mail->setStatus($mailStatus);
        $recipient = (new Recipient())->setToEmail('to@email.com')->setStatus(Recipient::STATUS_SENT);
        $mail->addRecipient($recipient);

        self::assertFalse($this->getMailResender()->forceResend($recipient));
        self::assertSame(Recipient::STATUS_SENT, $recipient->getStatus());
        self::assertSame($mailStatus, $mail->getStatus());
        self::assertSame([], $this->persisted);
    }

    public function testResendRecipientsIgnoresCancelledMail(): void
    {
        $mail = new Mail();
        $mail->setId(1);
        $mail->setStatus(Mail::STATUS_CANCELLED);
        $recipient = (new Recipient())->setToEmail('to@email.com')->setStatus(Recipient::STATUS_ERROR);
        $mail->addRecipient($recipient);

        self::assertSame(0, $this->getMailResender()->resendRecipients([$recipient]));
        self::assertSame(Recipient::STATUS_ERROR, $recipient->getStatus());
        self::assertSame(Mail::STATUS_CANCELLED, $mail->getStatus());
        self::assertSame([], $this->persisted);
    }

    protected function getMailResender(): MailResender
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('persist')->willReturnCallback(function (Recipient $recipient): void {
            $this->persisted[] = $recipient;
        });

        return new MailResender($em, new RecipientFactory());
    }

    protected function getSender(): Sender
    {
        return new Sender(
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(EmailErrorRepository::class),
            $this->createMock(MailerInterface::class),
            $this->createMock(MailBuilder::class),
            $this->createMock(ParameterBagInterface::class),
            $this->createMock(RecipientRepository::class),
            $this->createMock(UnsubscribeEmailRepository::class),
            $this->createMock(ErrorLogger::class),
        );
    }
}
