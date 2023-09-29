<?php

namespace Lle\HermesBundle\Service;

use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Entity\Template;
use Lle\HermesBundle\Model\MailDto;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;

class MailFactory
{
    public const ATTACHMENTS_DIR = "/data/hermes/attachments/mail-%s/";

    public function __construct(
        protected readonly ParameterBagInterface $parameters,
        protected readonly DestinataireFactory $destinataireFactory,
    ) {
    }

    public function createMailFromDto(MailDto $mailDto, Template $template): Mail
    {
        $mail = new Mail();
        $mail->setTemplate($template);
        $mail->setCreatedAt(new \DateTime());

        $nbDest = 0;
        foreach ($mailDto->getTo() as $contactDto) {
            $dest = $this->destinataireFactory->createDestinataireFromData($contactDto);
            $mail->addRecipient($dest);
            $nbDest++;
        }
        foreach ($mailDto->getCc() as $ccDto) {
            $dest = $this->destinataireFactory->createDestinataireFromData($ccDto);
            $mail->addCcRecipient($dest);
            $nbDest++;
        }

        $mail->setData($mailDto->getData());
        $mail->setTotalToSend($nbDest);
        $mail->setTotalSended(0);
        $mail->setSubject($mail->getTemplate()->getSubject());
        if ($mailDto->isSendText()) {
            $mail->setText($mail->getTemplate()->getText());
        }
        if ($mailDto->isSendHtml()) {
            $mail->setHtml($mail->getTemplate()->getHtml());
        }

        return $mail;
    }

    public function saveAttachments(MailDto $mailDto, Mail $mail): void
    {
        $attachmentsArray = [];

        /** @var string $rootPath */
        $rootPath = $this->parameters->get('lle_hermes.root_dir');

        foreach ($mailDto->getAttachments() as $attachment) {
            $path = sprintf($rootPath . self::ATTACHMENTS_DIR, $mail->getId());

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
