<?php

namespace App\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Entity\Recipient;
use Lle\HermesBundle\Service\ErrorLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Mailer\Exception\TransportException;

class ErrorLoggerTest extends TestCase
{
    public function testLogErrorWritesDetailedContextWithoutSecrets(): void
    {
        $mail = new Mail();
        $mail->setSubject('Hello');
        $mail->setDsn('smtp://user:s3cr3t@smtp.example.com:587');
        $recipient = new Recipient();
        $recipient->setMail($mail);
        $recipient->setToEmail('john.doe@test.com');
        $recipient->setStatus(Recipient::STATUS_ERROR);

        $exception = new TransportException('Expected response code "250" but got "550".');
        $exception->appendDebug(
            "> EHLO localhost\r\n< 250 OK\r\n> AUTH LOGIN\r\n< 334 VXNlcm5hbWU6\r\n> dXNlcg==\r\n"
            . "< 334 UGFzc3dvcmQ6\r\n> czNjcjN0\r\n< 235 OK\r\n> RCPT TO:<john.doe@test.com>\r\n< 550 Unknown user\r\n"
        );

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('log')
            ->with(
                LogLevel::ERROR,
                'Transport error',
                self::callback(function (array $context): bool {
                    self::assertSame('john.doe@test.com', $context['recipient_email']);
                    self::assertSame('Hello', $context['subject']);
                    self::assertSame('smtp://user:***@smtp.example.com:587', $context['dsn']);
                    self::assertSame(TransportException::class, $context['exception_class']);
                    self::assertStringContainsString('550 Unknown user', $context['smtp_debug']);
                    self::assertStringNotContainsString('dXNlcg==', $context['smtp_debug']);
                    self::assertStringNotContainsString('czNjcjN0', $context['smtp_debug']);
                    self::assertStringContainsString('RCPT TO:<john.doe@test.com>', $context['smtp_debug']);

                    return true;
                })
            );

        $errorLogger = new ErrorLogger($this->createMock(EntityManagerInterface::class), $logger);
        $errorLogger->logError('Transport error', $recipient, $exception);

        self::assertSame('Transport error', $recipient->getErrorMessage());
    }

    public function testLogErrorIsAWarningWhenRecipientWillBeRetried(): void
    {
        $recipient = new Recipient();
        $recipient->setMail(new Mail());
        $recipient->setStatus(Recipient::STATUS_RETRY);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('log')->with(LogLevel::WARNING);

        $errorLogger = new ErrorLogger($this->createMock(EntityManagerInterface::class), $logger);
        $errorLogger->logError('Transport error', $recipient);
    }
}
