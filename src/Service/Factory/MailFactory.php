<?php

namespace Lle\HermesBundle\Service\Factory;

use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Entity\Template;
use Lle\HermesBundle\Contracts\MultiTenantInterface;
use Lle\HermesBundle\Model\MailDto;
use Lle\HermesBundle\Service\MultiTenantManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;

use function Symfony\Component\Translation\t;

class MailFactory
{
    public function __construct(
        protected readonly multiTenantManager $multiTenantManager,
        protected readonly ParameterBagInterface $parameters,
        protected readonly RecipientFactory $recipientFactory,
        protected readonly Security $security,
    ) {
    }

    public function createMailFromDto(MailDto $mailDto, Template $template): Mail
    {
        $mail = new Mail();
        $mail->setTemplate($template);
        $mail->setCreatedAt(new \DateTime());

        $tenantId = null;
        if ($this->multiTenantManager->isMultiTenantEnabled()) {
            if ($mailDto->getTenantId()) {
                $tenantId = $mailDto->getTenantId();
            } else {
                $tenantId = $this->multiTenantManager->getTenantId();
            }
        }
        if ($tenantId) {
            $mail->setTenantId($tenantId);
        }

        $nbDest = 0;
        foreach ($mailDto->getTo() as $contactDto) {
            $recipient = $this->recipientFactory->createRecipientFromDto($contactDto, $tenantId);
            $mail->addRecipient($recipient);
            $nbDest++;
        }
        foreach ($mailDto->getCc() as $ccDto) {
            $recipient = $this->recipientFactory->createRecipientFromDto($ccDto, $tenantId);
            $mail->addCcRecipient($recipient);
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

        $mail->setSendAtDate($mailDto->getSendAt());
        $mail->setDsn($mailDto->getDsn());

        return $mail;
    }
}
