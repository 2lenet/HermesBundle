<?php

namespace Lle\HermesBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Entity\Recipient;
use Lle\HermesBundle\Enum\StatusEnum;
use Lle\HermesBundle\Repository\MailRepository;
use Lle\HermesBundle\Repository\RecipientRepository;
use Lle\HermesBundle\Repository\UnsubscribeEmailRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
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
        MailerInterface            $mailer,
        EntityManagerInterface     $entityManager,
        RecipientRepository        $recipientRepository,
        UnsubscribeEmailRepository $unsubscribeEmailRepository,
        MailRepository             $mailRepository,
        ParameterBagInterface      $parameterBag,
        RouterInterface            $router,
        Environment                $twig
    )
    {
        $this->mailer = $mailer;
        $this->entityManager = $entityManager;
        $this->recipientRepository = $recipientRepository;
        $this->unsubscribeEmailRepository = $unsubscribeEmailRepository;
        $this->mailRepository = $mailRepository;
        $this->parameterBag = $parameterBag;
        $this->router = $router;
        $this->twig = $twig;
    }

    public function sendAllMail(int $limit = 10): void
    {
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
                    $recipient->setStatus('unsubscribed');
                } else {
                    $this->send($mail, $recipient);
                }
            } else {
                $this->send($mail, $recipient);
            }
        }
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

    protected function send(Mail $mail, Recipient $recipient): void
    {
        try {
            $this->mailer->send($this->buildMail($mail, $recipient));
            $recipient->setStatus('sent');
            $this->entityManager->persist($recipient);
            $this->entityManager->flush();
            $this->updateMailAndRecipient($mail);
        } catch (TransportException $transportException) {
            echo $transportException->getMessage();
        }
    }

    protected function buildMail(Mail $mail, Recipient $recipient): TemplatedEmail
    {
        $template = $mail->getTemplate();
        $subjectTemplate = $this->getSubjectTemplate($mail);
        $htmlTemplate = $this->getHtmlTemplate($mail);
        $data = [];

        /** @var string $rootDir */
        $rootDir = $this->parameterBag->get('lle_hermes.rootDir');
        $attachmentsFilePath = sprintf(
            '%s/data/attachments/mail-%s/',
            $rootDir,
            $mail->getId()
        );

        if ($mail->getData()) {
            $data = array_merge($data, $mail->getData());
        }
        if ($recipient->getData()) {
            $data = array_merge($data, $recipient->getData());
            $data = array_merge($data, ['DEST_ID' => $recipient->getId()]);
        }

        /** @var string $domain */
        $domain = $this->parameterBag->get('lle_hermes.app_domain');

        // Generate unsubscribe link
        /** @var string $secret */
        $secret = $this->parameterBag->get('lle_hermes.appSecret');
        $token = md5($recipient->getToEmail() . $secret);
        $context = $this->router->getContext();
        $context->setHost($domain);
        $context->setScheme('https');
        $link = $this->router->generate(
            'unsubscribe',
            ['email' => $recipient->getToEmail(), 'token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $unsubscribeLink = ['UNSUBSCRIBE_LINK' => $link];
        $data = array_merge($data, $unsubscribeLink);

        /** @var string $sender */
        $sender = $this->parameterBag->get('lle_hermes.bounce.email');

        $email = (new TemplatedEmail())
            ->sender($sender)
            ->from(new Address($template->getSenderEmail(), $template->getSenderName() ?? ''))
            ->to(new Address($recipient->getToEmail(), $recipient->getToName() ?? ''))
            ->replyTo(new Address($template->getSenderEmail(), $template->getSenderName() ?? ''))
            ->subject($subjectTemplate->render($data))
            ->text((string)$mail->getText())
            ->html($htmlTemplate->render($data))
            ->context($data);

        if (count($mail->getAttachement()) > 0) {
            foreach ($mail->getAttachement() as $attachment) {
                $email->attachFromPath($attachmentsFilePath . $attachment['name']);
            }
        }
        $email = $this->attachBase64Img($email, $domain);
        return $email;
    }

    protected function getSubjectTemplate(Mail $mail): TemplateWrapper
    {
        return $this->twig->createTemplate($mail->getSubject());
    }

    protected function getHtmlTemplate(Mail $mail): TemplateWrapper
    {
        return $this->twig->createTemplate((string)$mail->getHtml());
    }

    protected function attachBase64Img(TemplatedEmail $email, string $domain): TemplatedEmail
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

        $this->entityManager->persist($mail);

        if ($mail->getTotalSended() == ($mail->getTotalToSend() - (count($unsubscribedMails) + count($errorMails)))) {
            $mail->setStatus('sent');
        }

        $this->entityManager->flush();
    }
}
