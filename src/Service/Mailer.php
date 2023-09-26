<?php

namespace Lle\HermesBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Exception\TemplateNotFoundException;
use Lle\HermesBundle\Model\MailDto;
use Lle\HermesBundle\Repository\TemplateRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class Mailer
{
    protected EntityManagerInterface $em;
    protected MailFactory $mailFactory;
    protected TemplateRepository $templateRepository;

    public function __construct(EntityManagerInterface $em, MailFactory $mailFactory, TemplateRepository $templateRepository)
    {
        $this->em = $em;
        $this->mailFactory = $mailFactory;
        $this->templateRepository = $templateRepository;
    }

    /**
     * Create and send a mail.
     * @throws TemplateNotFoundException
     */
    public function send(MailDto $mail, string $status = Mail::STATUS_SENDING): void
    {
        $template = $this->templateRepository->findOneBy(['code' => $mail->getTemplate()]);
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
}
