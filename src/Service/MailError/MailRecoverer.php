<?php

namespace Lle\HermesBundle\Service\MailError;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Lle\HermesBundle\Entity\EmailError;
use Lle\HermesBundle\Entity\Error;
use Lle\HermesBundle\Exception\ImapLoginException;
use stdClass;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class MailRecoverer
{
    protected readonly string $host;
    protected readonly string $port;
    protected readonly string $user;
    protected readonly string $password;
    protected Pop3Manager $mailServerManager;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MailAnalyzer $mailAnalyzer,
        ParameterBagInterface $parameters
    ) {
        /** @var string $host */
        $host = $parameters->get('lle_hermes.bounce_host');
        /** @var string $port */
        $port = $parameters->get('lle_hermes.bounce_port');
        /** @var string $user */
        $user = $parameters->get('lle_hermes.bounce_user');
        /** @var string $password */
        $password = $parameters->get('lle_hermes.bounce_password');

        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
    }

    public function recoverAll(int $limit = 50, string $folder = Pop3Manager::DEFAULT_FOLDER, bool $ssl = false): int
    {
        $this->mailServerManager = new Pop3Manager($this->host, $this->port, $this->user, $this->password);

        if (!$this->mailServerManager->login()) {
            throw new ImapLoginException();
        }

        $mails = $this->mailServerManager->getMails($limit);

        $nbRecovered = 0;
        foreach ($mails as $mail) {
            if ($this->recoverMail($mail)) {
                $nbRecovered++;
            }
        }

        $this->mailServerManager->logout();

        return $nbRecovered;
    }

    protected function recoverMail(stdClass $mail): bool
    {
        if (!$this->mailAnalyzer->isErrorMail($mail->subject)) {
            return false;
        }
        if (!isset($mail->uid) || !isset($mail->subject)) {
            return false;
        }

        $mailBody = $this->mailServerManager->getMailContent($mail->uid);
        if (!$mailBody) {
            return false;
        }

        $to = $this->getToEmail($mailBody);
        if (!$to) {
            return false;
        }

        $this->saveError($to, $mail->subject, $mailBody);

        $this->mailServerManager->deleteMail($mail->uid);

        return true;
    }

    protected function saveError(string $to, string $subject, ?string $mailBody = null): void
    {
        $emailError = $this->em->getRepository(EmailError::class)->findOneBy(['email' => $to]);

        if (!$emailError) {
            $emailError = new EmailError();
            $emailError->setEmail($to);
        }

        $emailError->incrementNbError();
        $this->em->persist($emailError);

        $error = new Error();
        $error->setDate(new DateTime())
            ->setSubject($subject)
            ->setContent($mailBody)
            ->setEmailError($emailError);
        $this->em->persist($error);

        $this->em->flush();
    }

    protected function getToEmail(string $mailBody): ?string
    {
        $mailBodyArray = preg_split('/\r\n/', $mailBody);
        if (!$mailBodyArray) {
            return null;
        }

        $emailLineArray = preg_grep('#^Original-Recipient:#', $mailBodyArray);
        if (!$emailLineArray) {
            return null;
        }

        $emailLine = implode(',', $emailLineArray);

        return substr($emailLine, strpos($emailLine, ';') + 1, strlen($emailLine));
    }
}
