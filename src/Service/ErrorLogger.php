<?php

namespace Lle\HermesBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\HermesBundle\Entity\Recipient;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Throwable;

class ErrorLogger
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected ?LoggerInterface $logger = null,
    ) {
    }

    public function logError(string $message, Recipient $recipient, ?Throwable $exception = null): void
    {
        $recipient->setErrorMessage($message);

        $this->em->flush();

        // A recipient in retry will be sent again later: it is not an error yet.
        $level = $recipient->getStatus() === Recipient::STATUS_RETRY ? LogLevel::WARNING : LogLevel::ERROR;
        $this->logger?->log($level, $message, $this->buildContext($recipient, $exception));
    }

    /**
     * @return array<string, mixed>
     */
    public function buildContext(Recipient $recipient, ?Throwable $exception = null): array
    {
        $mail = $recipient->getMail() ?? $recipient->getCcMail();
        $dsn = $mail?->getDsn();

        $context = [
            'recipient_id' => $recipient->getId(),
            'recipient_email' => $recipient->getToEmail(),
            'recipient_status' => $recipient->getStatus(),
            'retry_count' => $recipient->getRetryCount(),
            'mail_id' => $mail?->getId(),
            'mail_status' => $mail?->getStatus(),
            'subject' => $mail?->getSubject(),
            'template' => $mail?->getTemplate()?->getCode(),
            'dsn' => $dsn ? $this->maskDsn($dsn) : 'default mailer',
        ];

        if ($exception) {
            $context['exception_class'] = $exception::class;
            $context['exception_message'] = $exception->getMessage();
            $context['exception_code'] = $exception->getCode();
            $previous = $exception->getPrevious();
            if ($previous) {
                $context['previous_exception'] = $previous::class . ': ' . $previous->getMessage();
            }
            if ($exception instanceof TransportExceptionInterface && $exception->getDebug()) {
                // SMTP dialog with the server: the most useful information to understand a refusal
                $context['smtp_debug'] = $this->maskSmtpDebug($exception->getDebug());
            }
            $context['trace'] = $exception->getTraceAsString();
        }

        return $context;
    }

    protected function maskDsn(string $dsn): string
    {
        return (string)preg_replace('#^([^:]+://[^:@/]*):[^@]*@#', '$1:***@', $dsn);
    }

    protected function maskSmtpDebug(string $debug): string
    {
        $lines = explode("\n", $debug);
        $authInProgress = false;

        foreach ($lines as $i => $line) {
            if (preg_match('/^> AUTH\s+(\S+)/i', $line, $matches)) {
                $lines[$i] = '> AUTH ' . $matches[1] . ' ***';
                $authInProgress = true;
            } elseif ($authInProgress && str_starts_with($line, '> ')) {
                // Credentials sent in response to a 334 challenge
                $lines[$i] = '> ***';
            } elseif (str_starts_with($line, '< ') && !str_starts_with($line, '< 334')) {
                $authInProgress = false;
            }
        }

        return implode("\n", $lines);
    }
}
