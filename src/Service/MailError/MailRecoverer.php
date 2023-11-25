<?php

namespace Lle\HermesBundle\Service\MailError;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Lle\HermesBundle\Entity\EmailError;
use Lle\HermesBundle\Entity\Error;
use stdClass;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class MailRecoverer
{
    protected readonly string $host;
    protected readonly string $port;
    protected readonly string $user;
    protected readonly string $password;
    protected readonly Pop3Manager $mailServerManager;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MailAnalyzer $mailAnalyzer,
        ParameterBagInterface $parameters
    ) {
        $host = $parameters->get('lle_hermes.bounce_host');
        $port = $parameters->get('lle_hermes.bounce_port');
        $user = $parameters->get('lle_hermes.bounce_user');
        $password = $parameters->get('lle_hermes.bounce_password');

        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
    }

    public function recoverAll(int $limit = 50, string $folder = Pop3Manager::DEFAULT_FOLDER, bool $ssl = false): ?int
    {
        $this->mailServerManager = new Pop3Manager($this->host, $this->port, $this->user, $this->password);

        if (!$this->mailServerManager->login()) {
            return null;
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

    protected function recoverMail(stdClass $mail)
    {
        if (!$this->mailAnalyzer->isErrorMail($mail->subject)) {
            return false;
        }
        if (!isset($mail->to) || !isset($mail->subject)) {
            return false;
        }

        $to = $this->getToEmail($mail->to);
        $mailBody = $this->mailServerManager->getMailContent($mail->uid);
        $this->saveError($to, $mail->subject, $mailBody);

        $this->mailServerManager->deleteMail($mail->uid);

        return true;
    }

    protected function saveError(string $to, string $subject, string $mailBody): void
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

    protected function getToEmail(string $to): string
    {
        $arrayTo = explode('<', $to);
        if (!array_key_exists(1, $arrayTo)) {
            return rtrim($to, '>');
        }

        return rtrim($arrayTo[1], '>');
    }
}
