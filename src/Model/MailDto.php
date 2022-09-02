<?php

namespace Lle\HermesBundle\Model;

use Lle\HermesBundle\Enum\StatusEnum;
use Lle\HermesBundle\Model\ContactDto;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class Mail
 * @package Lle\HermesBundle\Entity
 * Represents a Hermes mail.
 */
class MailDto
{
    const SENDING = StatusEnum::SENDING;

    const DRAFT = StatusEnum::DRAFT;

    /**
     * @var int
     * Mail's identifier. Can be custom
     */
    protected $id;

    /**
     * @var string
     * The subject of the mail.
     */
    protected $subject;

    /**
     * @var string
     * Content of the mail.
     */
    protected $textContent;

    /**
     * @var string
     * Html content of the mail.
     */
    protected $htmlContent;

    /**
     * @var ContactDto[]
     * People that should receive the mail.
     */
    protected $to = [];

    /**
     * @var ContactDto
     * The person that sends the mail.
     */
    protected $from;

    /**
     * @var string
     * The code of the template to use.
     */
    protected $template;

    /**
     * @var AttachmentInterface[]
     * Mail attachments.
     * Be careful about user files though. (@see https://symfony.com/doc/current/controller/upload_file.html)
     */
    protected $attachments = [];

    protected $status = StatusEnum::SENDING;

    /**
     * @var array
     * Data to use for mail template
     */
    protected $data = [];

    protected bool $sendHtml = true;

    /**
     * @return bool
     */
    public function isSendHtml(): bool
    {
        return $this->sendHtml;
    }

    /**
     * @param bool $sendHtml
     */
    public function setSendHtml(bool $sendHtml): void
    {
        $this->sendHtml = $sendHtml;
    }

    /**
     * @return bool
     */
    public function isSendText(): bool
    {
        return $this->sendText;
    }

    /**
     * @param bool $sendText
     */
    public function setSendText(bool $sendText): void
    {
        $this->sendText = $sendText;
    }

    protected bool $sendText = true;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubject(): ?string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject(?string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return string
     */
    public function getTextContent(): ?string
    {
        return $this->textContent;
    }

    /**
     * @param string $textContent
     */
    public function setTextContent(?string $textContent): self
    {
        $this->textContent = $textContent;

        return $this;
    }

    /**
     * @return string
     */
    public function getHtmlContent(): ?string
    {
        return $this->htmlContent;
    }

    /**
     * @param string $htmlContent
     */
    public function setHtmlContent(?string $htmlContent): self
    {
        $this->htmlContent = $htmlContent;

        return $this;
    }

    /**
     * @return ContactDto[]
     */
    public function getTo(): ?array
    {
        return $this->to;
    }

    /**
     * @param ContactDto[] $to
     */
    public function setTo(?array $to): self
    {
        $this->to = $to;

        return $this;
    }

    /**
     * @param ContactDto $to
     */
    public function addTo(?ContactDto $to): self
    {
        $this->to[] = $to;

        return $this;
    }

    /**
     * @return ContactDto
     */
    public function getFrom(): ?ContactDto
    {
        return $this->from;
    }

    /**
     * @param ContactDto $from
     */
    public function setFrom(?ContactDto $from): self
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate(): ?string
    {
        return $this->template;
    }

    /**
     * @param string $template
     */
    public function setTemplate(?string $template): self
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @return AttachmentInterface[]
     */
    public function getAttachments(): ?array
    {
        return $this->attachments;
    }

    /**
     * @param AttachmentInterface[] $attachments
     */
    public function setAttachments(?array $attachments): self
    {
        $this->attachments = $attachments;

        return $this;
    }

    public function addAttachment(AttachmentInterface $attachment): self
    {
        $this->attachments[] = $attachment;

        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return MailDto
     */
    public function setStatus(string $status): MailDto
    {
        $this->status = $status;
        return $this;
    }
}
