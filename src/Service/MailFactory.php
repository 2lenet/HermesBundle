<?php

namespace Lle\HermesBundle\Service;

use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Model\MailDto;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class MailFactory
{
    public const ATTACHMENTS_DIR = "/data/hermes/attachments/mail-%s/";

    protected ParameterBagInterface $parameters;

    public function __construct(ParameterBagInterface $parameters)
    {
        $this->parameters = $parameters;
    }

    public function createMailFromDto(MailDto $mailDto, $template): ?Mail
    {
        $mail = new Mail();
        $mail->setTemplate($template);
        $mail->setStatus($mailDto->getStatus());
        $mail->setCreatedAt(new \DateTime('now'));
        $nbDest = 0;
        $destFactory = new DestinataireFactory();
        foreach ($mailDto->getTo() as $contactDto) {
            $dest = $destFactory->createDestinataireFromData($contactDto);
            $mail->addRecipient($dest);
            $nbDest++;
        }
        $mail->setData($mailDto->getData());
        $mail->setTotalToSend($nbDest);
        $mail->setTotalSended(0);
        $mail->setSubject($mail->getTemplate()->getSubject());
        $mail->setMjml($mail->getTemplate()->getMjml());
        $mail->setHtml($mail->getTemplate()->getHtml());
        $mail->setText($mail->getTemplate()->getText());

        return $mail;
    }

    public function updateHtml(Mail $mail)
    {
        $twig = '';
        try {
            $process = new Process([__DIR__ . '/../../node_modules/.bin/mjml', '-i']);
            $process->setInput($mail->getMjml());
            $out = $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            $twig = $process->getOutput();
        } catch (ProcessFailedException $exception) {
            echo $exception->getMessage();
            die();
        }
        $mail->setHtml($twig);

        return $mail;
    }

    public function saveAttachments(MailDto $mailDto, Mail $mail): void
    {
        $attachmentsArray = [];

        foreach ($mailDto->getAttachments() as $attachment) {
            $path = sprintf($this->parameters->get("lle_hermes.root_dir") . self::ATTACHMENTS_DIR, $mail->getId());

            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }

            file_put_contents($path . $attachment->getName(), $attachment->getData());
            $attachmentsArray[] = [
                "path" => $path,
                "name" => $attachment->getName(),
                "content-type" => $attachment->getContentType(),
            ];
        }

        $mail->setAttachement($attachmentsArray);
    }
}
