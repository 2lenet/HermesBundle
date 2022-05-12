<?php

namespace Lle\HermesBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Entity\Recipient;
use Lle\HermesBundle\Enum\StatusEnum;
use Lle\HermesBundle\Exception\NoRecipientException;
use Lle\HermesBundle\Repository\MailRepository;
use Lle\HermesBundle\Repository\RecipientRepository;
use Lle\HermesBundle\Repository\UnsubscribeEmailRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\TemplateWrapper;

/**
 * Class SenderService
 * @package Lle\HermesBundle\Service
 *
 * @author 2LE <2le@2le.net>
 */
class SenderService
{
    private MailerInterface $mailer;
    private EntityManagerInterface $entityManager;
    private RecipientRepository $recipientRepository;
    private UnsubscribeEmailRepository $unsubscribeEmailRepository;
    private MailRepository $mailRepository;
    private ParameterBagInterface $parameterBag;
    private RouterInterface $router;
    private Environment $twig;

    public function __construct(
        MailerInterface $mailer,
        EntityManagerInterface $entityManager,
        RecipientRepository $recipientRepository,
        UnsubscribeEmailRepository $unsubscribeEmailRepository,
        MailRepository $mailRepository,
        ParameterBagInterface $parameterBag,
        RouterInterface $router,
        Environment $twig
    ) {
        $this->mailer = $mailer;
        $this->entityManager = $entityManager;
        $this->recipientRepository = $recipientRepository;
        $this->unsubscribeEmailRepository = $unsubscribeEmailRepository;
        $this->mailRepository = $mailRepository;
        $this->parameterBag = $parameterBag;
        $this->router = $router;
        $this->twig = $twig;
    }

    public function sendAllMail(int $limit = 10): int
    {
        $nb = 0;
        $this->recipientRepository->disableErrors();

        $this->setMailTotalError();

        $unsubscribedArray = $this->unsubscribeEmailRepository->findEmailUnsubscribed();

        $recipients = $this->recipientRepository
            ->findRecipientsSending('ok', 'sending', $limit);
        foreach ($recipients as $recipient) {
            $mail = $recipient->getMail();
            $template = $mail->getTemplate();

            // Unsubscriptions are disabled depending on whether the email template takes them into account or not.
            if ($template->isUnsubscriptions() == true) {
                if (in_array($recipient->getToEmail(), array_column($unsubscribedArray, 'email'))) {
                    $recipient->setStatus(StatusEnum::UNSUBSCRIBED);
                } else {
                    if ($this->send($mail, $recipient)) {
                        $nb++;
                    } else {
                        print("error sending to ". $recipient);
                    }
                }
            } else {
                if ($this->send($mail, $recipient)) {
                    $nb++;
                } else {
                    print("error sending to ". $recipient);
                }
            }
        }
        return $nb;
    }

    protected function setMailTotalError(): void
    {
        $mails = $this->mailRepository->findByStatus('sending');
        foreach ($mails as $mail) {
            $recipientsError = $this->recipientRepository->findBy(
                ['status' => StatusEnum::ERROR, 'mail' => $mail->getId()]
            );
            $mail->setTotalError(count($recipientsError));
            $this->entityManager->persist($mail);
        }
        $this->entityManager->flush();
    }

    protected function send(Mail $mail, Recipient $recipient): bool
    {
        try {
            $this->mailer->send($this->buildMail($mail, $recipient));
            $recipient->setStatus(StatusEnum::SENT);
            $this->entityManager->persist($recipient);
            $this->entityManager->flush();
            $this->updateMailAndRecipient($mail);
            return true;
        } catch (TransportException $transportException) {
            $recipient->setStatus(StatusEnum::ERROR);
            return false;
        }
    }

    protected function buildMail(Mail $mail, Recipient $recipient): Email
    {
        $templater = new MailTemplater($mail, $this->twig, $this->router);

        /** @var string $rootDir */
        $rootDir = $this->parameterBag->get('lle_hermes.root_dir');
        $attachmentsFilePath = $rootDir . sprintf(
            MailFactory::ATTACHMENTS_DIR,
            $mail->getId()
        );

        $templater->addData($mail->getData());
        $templater->addData($recipient->getData());
        $templater->addData(["DEST_ID" => $recipient->getId()]);

        /** @var string $domain */
        $domain = $this->parameterBag->get('lle_hermes.app_domain');

        // Generate unsubscribe link
        /** @var string $secret */
        $secret = $this->parameterBag->get('lle_hermes.app_secret');
        $token = md5($recipient->getToEmail() . $secret);
        $context = $this->router->getContext();
        $context->setHost($domain);
        $context->setScheme('https');
        $link = $this->router->generate(
            'unsubscribe',
            ['email' => $recipient->getToEmail(), 'token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $templater->addData(["UNSUBSCRIBE_LINK" => $link]);

        /** @var string $sender */
        $sender = $this->parameterBag->get('lle_hermes.bounce_email');

        $from = new Address($mail->getTemplate()->getSenderEmail(), $mail->getTemplate()->getSenderName() ?? "");

        $email = new Email();

        if (!$recipient->getMail() && !$recipient->getCcMail()) {
            throw new NoRecipientException($mail->getId());
        }

        if ($recipient->getMail()) {
            $to = new Address($recipient->getToEmail(), $recipient->getToName() ?? "");
            $email->to($to);
        }

        if ($recipient->getCcMail()) {
            $cc = new Address($recipient->getToEmail(), $recipient->getToName() ?? "");
            $email->addCc($cc);
        }

        $email
            ->sender($sender)
            ->from($from)
            ->replyTo($from)
            ->subject($templater->getSubject())
            ->text($templater->getText())
            ->html($templater->getHtml());

        if (count($mail->getAttachement()) > 0) {
            foreach ($mail->getAttachement() as $attachment) {
                $email->attachFromPath($attachmentsFilePath . $attachment['name']);
            }
        }
        $email = $this->attachBase64Img($email, $domain);

        return $email;
    }

    protected function attachBase64Img(Email $email, string $domain): Email
    {
        $newHtml = preg_replace_callback(
            '/src\s*=\s*"data:image\/(png|jpg|jpeg|gif);base64,(.*?)"/i',
            function ($matches) use ($domain) {
                $content = base64_decode($matches[2]);
                $filenamePath = md5(time() . uniqid('', true)) . ".jpg";
                /** @var string $rootDir */
                $rootDir = $this->parameterBag->get('lle_hermes.root_dir');
                $filename = $rootDir . '/public/upload/images/' . $filenamePath;
                file_put_contents($filename, $content);
                return 'src="https://' . $domain . '/upload/images/' . $filenamePath . '"';
            },
            (string)$email->getHtmlBody()
        );

        $email->html($newHtml);

        return $email;
    }

    protected function updateMailAndRecipient(Mail $mail): void
    {
        $destinataireSent = $this->recipientRepository
            ->findBy(['status' => StatusEnum::SENT, 'mail' => $mail]);
        $mail->setTotalSended(count($destinataireSent));

        $unsubscribedMails = $this->recipientRepository
            ->findBy(['status' => StatusEnum::UNSUBSCRIBED, 'mail' => $mail]);
        $mail->setTotalUnsubscribed(count($unsubscribedMails));

        $errorMails = $this->recipientRepository
            ->findBy(['status' => StatusEnum::ERROR, 'mail' => $mail]);
        $mail->setTotalError(count($errorMails));

        $total = $mail->getTotalToSend() - $mail->getTotalUnsubscribed() + $mail->getTotalError();
        if ($mail->getTotalSended() == $total) {
            $mail->setStatus(StatusEnum::SENT);
        }

        $this->entityManager->persist($mail);

        $this->entityManager->flush();
    }
}
