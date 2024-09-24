<?php

namespace Lle\HermesBundle\Model;

use Lle\HermesBundle\Contracts\AttachmentInterface;
use Lle\HermesBundle\Entity\Mail;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class Mail
 * @package Lle\HermesBundle\Entity
 * Represents a Hermes mail.
 */
class MailDto
{
    public const DRAFT = Mail::STATUS_DRAFT;
    public const SENDING = Mail::STATUS_SENDING;

    /**
     * The subject of the mail.
     */
    protected string $subject;

    /**
     * @var ContactDto[]
     * People that should receive the mail.
     */
    protected array $to = [];

    /**
     * @var ContactDto[]
     * People in copy
     */
    protected array $cc = [];

    /**
     * The person that sends the mail.
     */
    protected ContactDto $from;

    /**
     * The code of the template to use.
     */
    protected string $template;

    /**
     * @var AttachmentInterface[]
     * Mail attachments.
     * Be careful about user files though. (@see https://symfony.com/doc/current/controller/upload_file.html)
     */
    protected array $attachments = [];

    protected string $status = Mail::STATUS_SENDING;

    /**
     * Data to use for mail template
     */
    protected array $data = [];

    protected bool $sendHtml = true;

    protected bool $sendText = true;

    protected ?int $tenantId = null;

    protected ?\DateTime $sendAt = null;

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return ContactDto[]
     */
    public function getTo(): array
    {
        return $this->to;
    }

    /**
     * @param ContactDto[] $to
     */
    public function setTo(array $to): self
    {
        $this->to = $to;

        return $this;
    }

    public function addTo(ContactDto $to): self
    {
        $this->to[] = $to;

        return $this;
    }

    /**
     * @return ContactDto[]
     */
    public function getCc(): array
    {
        return $this->cc;
    }

    /**
     * @param ContactDto[] $cc
     */
    public function setCc(array $cc): self
    {
        $this->cc = $cc;

        return $this;
    }

    public function addCc(ContactDto $cc): self
    {
        $this->cc[] = $cc;

        return $this;
    }

    public function getFrom(): ContactDto
    {
        return $this->from;
    }

    public function setFrom(ContactDto $from): self
    {
        $this->from = $from;

        return $this;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function setTemplate(string $template): self
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @return AttachmentInterface[]
     */
    public function getAttachments(): array
    {
        return $this->attachments;
    }

    /**
     * @param AttachmentInterface[] $attachments
     */
    public function setAttachments(array $attachments): self
    {
        $this->attachments = $attachments;

        return $this;
    }

    public function addAttachment(AttachmentInterface $attachment): self
    {
        $this->attachments[] = $attachment;

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function isSendHtml(): bool
    {
        return $this->sendHtml;
    }

    public function setSendHtml(bool $sendHtml): void
    {
        $this->sendHtml = $sendHtml;
    }

    public function isSendText(): bool
    {
        return $this->sendText;
    }

    public function setSendText(bool $sendText): void
    {
        $this->sendText = $sendText;
    }

    public function getTenantId(): ?int
    {
        return $this->tenantId;
    }

    public function setTenantId(?int $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function getSendAt(): ?\DateTime
    {
        return $this->sendAt;
    }

    public function setSendAt(?\DateTime $sendAt): self
    {
        $this->sendAt = $sendAt;

        return $this;
    }
}
