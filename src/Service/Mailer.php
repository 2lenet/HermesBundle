<?php

namespace Lle\HermesBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Exception\TemplateNotFoundException;
use Lle\HermesBundle\Contracts\MultiTenantInterface;
use Lle\HermesBundle\Model\MailDto;
use Lle\HermesBundle\Repository\TemplateRepository;
use Lle\HermesBundle\Service\Factory\MailFactory;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class Mailer
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly MailFactory $mailFactory,
        protected readonly MultiTenantManager $multiTenantManager,
        protected readonly TemplateRepository $templateRepository,
        protected readonly ParameterBagInterface $parameterBag,
        protected readonly Security $security,
    ) {
    }

    /**
     * @throws TemplateNotFoundException
     */
    public function create(MailDto $mail, string $status = Mail::STATUS_SENDING): void
    {
        $template = null;
        if ($this->multiTenantManager->isMultiTenantEnabled()) {
            $tenantId = $this->multiTenantManager->getTenantId();
            $template = $this->templateRepository->findOneBy(['code' => $mail->getTemplate(), 'tenantId' => $tenantId]);
        }

        if (!$template) {
            $template = $this->templateRepository->findOneBy(['code' => $mail->getTemplate(), 'tenantId' => null]);
        }
        if (!$template) {
            throw new TemplateNotFoundException($mail->getTemplate());
        }

        $mailObj = $this->mailFactory->createMailFromDto($mail, $template);

        $mailObj->setStatus(Mail::STATUS_DRAFT);
        $this->em->persist($mailObj);
        $this->em->flush();

        $this->mailFactory->saveAttachments($mail, $mailObj);
        // set status AFTER because we need the mail ID for attachments
        // and we don't want Hermès to send a mail without its attachments
        $mailObj->setStatus($status);
        $this->em->persist($mailObj);
        $this->em->flush();
    }

    /**
     * @deprecated Use create() method instead
     */
    public function send(MailDto $mail, string $status = Mail::STATUS_SENDING): void
    {
        $this->create($mail, $status);
    }
}
