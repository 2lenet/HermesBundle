<?php

namespace Lle\HermesBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\HermesBundle\Exception\MailerException;
use Lle\HermesBundle\Model\Mail;
use Lle\HermesBundle\Entity\Template;
use Lle\HermesBundle\Model\MailDto;
use Lle\HermesBundle\Service\MailFactory;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * This class handles 'mail' API calls to Hermès.
 */
class Mailer
{
    public function __construct(EntityManagerInterface $em, MailFactory $mailerFactory) {
        $this->em = $em;
        $this->mailerFactory = $mailerFactory;
    }

    /**
     * Create a mail using Hermes (doesn't send)
     * @param Mail $mail
     * @return array
     */
    public function create(Mail $mail)
    {
        $url = $this->urlHermes . "/mail/create";
        $response = $this->makeRequest("POST", $url, [
            "body" => json_encode($this->serializeMail($mail)),
        ]);

        $responseData = json_decode($response->getContent(), true);
        $mail->setId($this->getIdFromHermesUrl($responseData["edit_link"]));

        return $responseData;
    }

    /**
     * Send a created mail.
     * @param Mail|int $mail Mail object or mail's id
     * @return array
     */
    public function sendSavedMail($mail)
    {
        if ($mail instanceof Mail) {
            $mail = $mail->getId();
        }

        if (!$mail) {
            throw new MailerException("You must create the mail before sending it!");
        }

        $url = $this->urlHermes . "/mail/$mail/prepare-send-mail";
        $response = $this->makeRequest("GET", $url);

        return json_decode($response->getContent(), true);
    }

    /**
     * Create and send a mail.
     * @param Mail $mail Mail object
     * @return array
     */
    public function send(MailDto $mail, $status = "draft")
    {
        $template = $this->em->getRepository(Template::class)->find($mail->getTemplate());
        $mailObj = $this->mailerFactory->createMailFromDto($mail, $template);
        $mailObj->setStatus($status);
        $this->em->persist($mailObj);
        $this->em->flush();
    }

    /**
     * Serialize a mail object (i.e. convert it to an array)
     * @param Mail $mail the mail to serialize
     * @return array the serialization result
     */
    public function serializeMail(Mail $mail): array
    {
        $tos = [];
        foreach ($mail->getTo() as $to) {
            $tos[] = [
                "toEmail" => $to->getAddress(),
                "toName" => $to->getName(),
            ];
        }

        $attachments = [];
        foreach ($mail->getAttachments() as $attachment) {
            $attachments[] = [
                "filename" => $attachment->getName(),
                "content" => $attachment->getBase64Data(),
                "content-type" => $attachment->getContentType(),
            ];
        }

        $res = [
            "destinataire" => $tos,
            "attachments" => $attachments,
            "Subject" => $mail->getSubject(),
            "TextPart" => $mail->getTextContent(),
            "HTMLPart" => $mail->getHtmlContent(),
            "data" => $mail->getData(),
        ];

        if ($mail->getFrom()) {
            $res["From"] = [
                "toEmail" => $mail->getFrom()->getAddress(),
                "toName" => $mail->getFrom()->getName(),
            ];
        }

        if ($mail->getTemplate()) {
            $res["template_code"] = $mail->getTemplate();
        }

        return $res;
    }

    /**
     * Gets the mail id (format api/url/id)
     * @param string $url
     * @return mixed|string
     */
    private function getIdFromHermesUrl(string $url)
    {
        $parts = explode("/", urldecode($url));
        return end($parts);
    }

    private function makeRequest($method, $url, $options = []): ResponseInterface
    {
        $response = $this->client->request($method, $url, $options);

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 400) {
            throw new MailerException("API call to $url failed: HTTP " . $response->getStatusCode());
        }

        return $response;
    }
}
