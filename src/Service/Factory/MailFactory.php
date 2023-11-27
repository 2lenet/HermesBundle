<?php

namespace Lle\HermesBundle\Service\Factory;

use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Entity\Template;
use Lle\HermesBundle\Interface\MultiTenantInterface;
use Lle\HermesBundle\Model\MailDto;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;

use function Symfony\Component\Translation\t;

class MailFactory
{
    public const ATTACHMENTS_DIR = "/data/hermes/attachments/mail-%s/";

    public function __construct(
        protected readonly ParameterBagInterface $parameters,
        protected readonly RecipientFactory $recipientFactory,
        protected readonly ParameterBagInterface $parameterBag,
        protected readonly Security $security,
    ) {
    }

    public function createMailFromDto(MailDto $mailDto, Template $template): Mail
    {
        $mail = new Mail();
        $mail->setTemplate($template);
        $mail->setCreatedAt(new \DateTime());

        $nbDest = 0;
        foreach ($mailDto->getTo() as $contactDto) {
            $recipient = $this->recipientFactory->createRecipientFromDto($contactDto);
            $mail->addRecipient($recipient);
            $nbDest++;
        }
        foreach ($mailDto->getCc() as $ccDto) {
            $recipient = $this->recipientFactory->createRecipientFromDto($ccDto);
            $mail->addCcRecipient($recipient);
            $nbDest++;
        }

        $mail->setData($mailDto->getData());
        $mail->setTotalToSend($nbDest);
        $mail->setTotalSended(0);
        $mail->setSubject($mail->getTemplate()->getSubject());

        if ($this->parameterBag->get('lle_hermes.tenant_class')) {
            if ($mailDto->getTenantId()) {
                $tenantId = $mailDto->getTenantId();
            } else {
                /** @var MultiTenantInterface $user */
                $user = $this->security->getUser();
                $tenantId = $user->getTenantId();
            }
            $mail->setTenantId($tenantId);
        }

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
