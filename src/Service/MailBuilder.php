<?php

namespace Lle\HermesBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\EntityFileBundle\Service\EntityFileLoader;
use Lle\HermesBundle\Crudit\Config\MailCrudConfig;
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

class MailBuilder
{
    protected readonly string $secret;

    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ParameterBagInterface $parameters,
        protected readonly RouterInterface $router,
        protected readonly Environment $twig,
        protected EntityFileLoader $entityFileLoader,
    ) {
        /** @var string $secret */
        $secret = $parameters->get('lle_hermes.app_secret');
        $this->secret = $secret;
    }

    /**
     * @throws NoRecipientException
     */
    public function buildMail(Mail $mail, Recipient $recipient): Email
    {
        $templater = new MailTemplater($mail, $this->twig, $this->router);

        $templater->addData($mail->getData());
        $templater->addData($recipient->getData());

        /** @var string $domain */
        $domain = $this->parameters->get('lle_hermes.app_domain');
        /** @var string $returnPath */
        $returnPath = $this->parameters->get('lle_hermes.bounce_user');
        if ($mail->getTemplate()?->getCustomBounceEmail()) {
            $returnPath = $mail->getTemplate()->getCustomBounceEmail();
        }

        $context = $this->router->getContext();
        $context->setHost($domain);
        $context->setScheme('https');

        if ($mail->getTemplate()?->isUnsubscriptions()) {
            $templater->addData(['UNSUBSCRIBE_LINK' => $this->getUnsubscribeLink($recipient)]);
        }

        if ($mail->getSenderEmailFromLocale($mail->getLocale()) && $mail->getSenderEmailFromLocale($mail->getLocale())) {
            $from = new Address($mail->getSenderEmailFromLocale($mail->getLocale()), $mail->getSenderNameFromLocale($mail->getLocale()));
        } else {
            $from = new Address((string)$mail->getTemplate()?->getSenderEmail(), $templater->getSenderName());
        }

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
            ->subject($templater->getSubject());

        if ($returnPath) {
            $email->returnPath($returnPath);
        }

        $html = $templater->getHtml();

        // Generate confirmation of receipt link
        $html = $this->generateReceiptConfirmationLink($html, $recipient);

        if ($mail->getTemplate()?->hasStatistics()) {
            $html = $this->generateStatsLinks($html, $mail, $recipient);
        }

        $email->text($templater->getText());

        $email->html($html);

        if (count($mail->getAttachement()) > 0) {
            foreach ($mail->getAttachement() as $attachment) {
                $email->attachFromPath($attachment['path'] . $attachment['name']);
            }
        }

        $manager = $this->entityFileLoader->get(MailCrudConfig::MAIL_ATTACHED_FILE_CONFIG);
        foreach ($manager->get($mail) as $file) {
            $email->attach($manager->read($file), $file->getName(), $file->getMimeType());
        }

        return $this->attachBase64Img($email, $domain);
    }

    private function getUnsubscribeLink(Recipient $recipient): string
    {
        $token = md5($recipient->getToEmail() . $this->secret);
        $link = $this->router->generate(
            'unsubscribe',
            ['email' => $recipient->getToEmail(), 'token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $link;
    }

    private function generateReceiptConfirmationLink(string $html, Recipient $recipient): string
    {
        $route = $this->router->generate(
            'mail_opened',
            ['recipient' => $recipient->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return str_replace(
            '</body>',
            '<img src="' . $route . '" alt="" /></body>',
            $html
        );
    }

    private function generateStatsLinks(string $html, Mail $mail, Recipient $recipient): ?string
    {
        return preg_replace_callback(
            '/<a(.*?)href="(.*?)"(.*?)>(.*?)<\/a>/s',
            function ($matches) use ($mail, $recipient) {
                if (!str_contains($matches[2], '/hermes/unsubscribe')) {
                    $link = $this->em->getRepository(Link::class)->findOneBy(['mail' => $mail, 'url' => $matches[2]]);
                    if (!$link) {
                        $link = new Link();
                        $link->setMail($mail);
                        $link->setUrl(htmlspecialchars_decode($matches[2]));
                        $this->em->persist($link);
                        $this->em->flush();
                    }

                    $route = $this->router->generate(
                        'statistics',
                        ['recipient' => $recipient->getId(), 'link' => $link->getId()],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );

                    return '<a' . $matches[1] . 'href="' . $route . '"' . $matches[3] . '>' . $matches[4] . '</a>';
                } else {
                    return $matches[0];
                }
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
                $filename = md5($matches[2]) . '.jpg';
                /** @var string $rootDir */
                $rootDir = $this->parameters->get('lle_hermes.root_dir');
                /** @var string $uploadPath */
                $uploadPath = $this->parameters->get('lle_hermes.upload_path');

                $filenamePath = $rootDir . '/public' . $uploadPath . $filename;
                if (!file_exists($filenamePath)) {
                    file_put_contents($filenamePath, $content);
                }

                return 'src="https://' . $domain . $uploadPath . $filename . '"';
            },
            (string)$email->getHtmlBody()
        );

        $email->html($html);

        return $email;
    }
}
