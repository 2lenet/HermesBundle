<?php

namespace Lle\HermesBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\HermesBundle\Entity\Link;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Entity\Recipient;
use Lle\HermesBundle\Exception\NoRecipientException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class MailBuilderService
{
    private Environment $twig;
    private RouterInterface $router;
    private ParameterBagInterface $parameterBag;
    private string $secret;
    private EntityManagerInterface $em;

    public function __construct(Environment $twig, RouterInterface $router, ParameterBagInterface $parameterBag, EntityManagerInterface $em)
    {
        $this->twig = $twig;
        $this->router = $router;
        $this->parameterBag = $parameterBag;
        $this->secret = $parameterBag->get('lle_hermes.app_secret');
        $this->em = $em;
    }

    public function buildMail(Mail $mail, Recipient $recipient): Email
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

        /** @var string $domain */
        $domain = $this->parameterBag->get('lle_hermes.app_domain');
        $returnPath = $this->parameterBag->get('lle_hermes.bounce_email');
        $context = $this->router->getContext();
        $context->setHost($domain);
        $context->setScheme('https');

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
            ->from($from)
            ->replyTo($from)
            ->subject($templater->getSubject())
            ->returnPath($returnPath)
        ;

        $html = $templater->getHtml();

        // Generate unsubscribe link
        if ($mail->getTemplate()->isUnsubscriptions()) {
            $html = $this->generateUnsubscribeLink($html, $recipient);
        }

        // Generate confirmation of receipt link
        $html = $this->generateReceiptConfirmationLink($html, $recipient);

        if ($mail->getTemplate()->hasStatistics()) {
            $html = $this->generateStatsLinks($html, $mail, $recipient);
        }

        if ($templater->getText()) {
            $email->text($templater->getText());
        }
        if ($templater->getHtml()) {
            $email->html($html);
        }

        if (count($mail->getAttachement()) > 0) {
            foreach ($mail->getAttachement() as $attachment) {
                $email->attachFromPath($attachmentsFilePath . $attachment['name']);
            }
        }
        return $this->attachBase64Img($email, $domain);
    }

    private function generateUnsubscribeLink(string $html, Recipient $recipient): string
    {
        $token = md5($recipient->getToEmail() . $this->secret);
        $link = $this->router->generate(
            'unsubscribe',
            ['email' => $recipient->getToEmail(), 'token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return str_replace(
            '{{ UNSUBSCRIBE_LINK }}',
            $link,
            $html
        );
    }

    private function generateReceiptConfirmationLink(string $html, Recipient $recipient): string
    {
        $route = $this->router->generate('mail_opened', ['recipient' => $recipient->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return str_replace(
            '</body>',
            '<img src="' . $route . '" alt="" /></body>',
            $html
        );
    }

    private function generateStatsLinks(string $html, Mail $mail, Recipient $recipient): string
    {
        return preg_replace_callback(
            '/<a(.*?)href="(.*?)"(.*?)>(.*?)<\/a>/s',
            function ($matches) use ($mail, $recipient) {
                $link = new Link();
                $link->setMail($mail);
                $link->setUrl($matches[2]);
                $this->em->persist($link);
                $this->em->flush();

                $route = $this->router->generate('statistics', ['recipient' => $recipient->getId(), 'link' => $link->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

                return '<a' . $matches[1] . 'href="' . $route . '"' . $matches[3] . '>' . $matches[4] . '</a>';
            },
            $html
        );
    }

    public function attachBase64Img(Email $email, string $domain): Email
    {
        $html = preg_replace_callback(
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

        $email->html($html);

        return $email;
    }
}
