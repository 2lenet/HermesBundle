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
        protected multiTenantManager $multiTenantManager,
        protected ParameterBagInterface $parameters,
        protected RecipientFactory $recipientFactory,
        protected Security $security,
    ) {
    }

    public function createMailFromDto(MailDto $mailDto, Template $template): Mail
    {
        $locale = $mailDto->getLocale();
        $mail = new Mail();
        $mail
            ->setLocale($locale)
            ->setTemplate($template)
            ->setCreatedAt(new \DateTime())
            ->setEntityClass($mailDto->getEntityClass())
            ->setEntityId($mailDto->getEntityId());

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

        if ($mailDto->getFrom()) {
            $mail->setSenderName($mailDto->getFrom()->getName());
            $mail->setSenderEmail($mailDto->getFrom()->getAddress());
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
        $mail->setSenderEmail($template->getSenderEmailFromLocale($locale));
        $mail->setSenderName($template->getSenderNameFromLocale($locale));
        $mail->setSubject($template->getSubjectFromLocale($locale));
        $mail->setMjml($template->getMjmlFromLocale($locale));

        if ($mailDto->isSendText()) {
            $mail->setText($template->getTextFromLocale($locale));
        }

        if ($mailDto->isSendHtml()) {
            $mail->setHtml($template->getHtmlFromLocale($locale));
        }

        $mail->setSendAtDate($mailDto->getSendAt());
        $mail->setDsn($mailDto->getDsn());

        return $mail;
    }
}
