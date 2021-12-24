<?php


namespace Lle\HermesBundle\Service;


use DateTime;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Entity\Recipient;
use Lle\HermesBundle\Entity\Template;
use Lle\HermesBundle\Enum\StatusEnum;
use Lle\HermesBundle\Exception\MailerException;
use Lle\HermesBundle\Model\Mail as MailModel;
use Lle\HermesBundle\Repository\TemplateRepository;

/**
 * Class MailerService
 * @package Lle\HermesBundle\Service
 *
 * @author 2LE <2le@2le.net>
 */
class MailerService
{

    private SenderService $senderService;
    private TemplateRepository $templateRepository;

    public function __construct(SenderService $senderService, TemplateRepository $templateRepository)
    {
        $this->senderService = $senderService;
        $this->templateRepository = $templateRepository;
    }

    /**
     * Create a mail using Hermes (doesn't send)
     * @param MailModel $mail
     * @return array
     */
    public function create(MailModel $mailModel)
    {
        $mail = new Mail();
        /** @var Template $template */
        $template = $this->templateRepository->findOneByCode($mailModel->getTemplate());
        $mail->setTemplate($template);
        $mail->setStatus(StatusEnum::DRAFT);
        $mail->setCreatedAt(new DateTime('now'));

        foreach ($mailModel->getTo() as $to) {
            $recipient = new Recipient();
            $recipient->setStatus(StatusEnum::DRAFT);
            $recipient->setToEmail($to->getAddress());
            $recipient->setToName($to->getName());
            $recipient->setNbRetry(0);
            $mail->addRecipient($recipient);
        }
        $mail->setData($mailModel->getData());
        $mail->setTotalToSend($mail->getRecipients()->count());
        $mail->setTotalSended(0);
        $mail->setSubject($mail->getTemplate()->getSubject());
        $mail->setMjml($mail->getTemplate()->getMjml());
        $mail->setHtml($mail->getTemplate()->getHtml());

        return [];
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
    public function send(Mail $mail)
    {
        $url = $this->urlHermes . "/mail/directly-send";
        $response = $this->makeRequest("POST", $url, [
            "body" => json_encode($this->serializeMail($mail)),
        ]);

        $responseData = json_decode($response->getContent(), true);
        $mail->setId($this->getIdFromHermesUrl($responseData["edit_link"]));

        return $responseData;
    }
}
