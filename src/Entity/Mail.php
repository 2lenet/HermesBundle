<?php

namespace Lle\HermesBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Lle\HermesBundle\Contracts\MultiTenantInterface;
use Lle\HermesBundle\Repository\MailRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MailRepository::class)]
#[ORM\Table(name: 'lle_hermes_mail')]
#[ORM\Index(name: 'tenant_id_idx', columns: ['tenant_id'])]
class Mail implements MultiTenantInterface
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SENDING = 'sending';
    public const STATUS_SENT = 'sent';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_ERROR = 'error';

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;

    #[ORM\ManyToOne(targetEntity: Template::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[Assert\NotBlank]
    protected ?Template $template;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    protected ?string $senderName = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Assert\Email]
    protected ?string $senderEmail = null;

    #[ORM\Column(type: 'json', nullable: false)]
    protected array $data = [];

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    protected string $status;

    #[ORM\Column(type: 'integer')]
    protected int $totalToSend = 0;

    #[ORM\Column(type: 'integer')]
    protected int $totalSended = 0;

    #[ORM\OneToMany(targetEntity: Recipient::class, mappedBy: 'mail', cascade: ['persist', 'remove'])]
    protected Collection $recipients;

    #[ORM\Column(type: 'string', length: 1024)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 1024)]
    protected string $subject;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?DateTime $sendingDate = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?DateTime $sendAtDate = null;

    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $text = null;

    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $html = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?DateTime $createdAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?DateTime $updatedAt = null;

    #[ORM\Column(type: 'integer')]
    protected int $totalUnsubscribed = 0;

    #[ORM\Column(type: 'integer')]
    protected int $totalError = 0;

    #[ORM\Column(type: 'json')]
    protected array $attachement = [];

    #[ORM\Column(type: 'integer')]
    protected int $totalOpened = 0;

    #[ORM\OneToMany(targetEntity: Recipient::class, mappedBy: 'ccMail', cascade: ['persist', 'remove'])]
    protected Collection $ccRecipients;

    #[ORM\OneToMany(targetEntity: Link::class, mappedBy: 'mail', cascade: ['persist', 'remove'])]
    protected Collection $links;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected ?int $tenantId = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $dsn = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    protected bool $attachmentsDeleted = false;

    #[ORM\Column(type: 'string', nullable: true)]
    protected ?string $entityClass = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected ?int $entityId = null;

    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $mjml = null;

    public function __construct()
    {
        $this->recipients = new ArrayCollection();
        $this->ccRecipients = new ArrayCollection();
        $this->links = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string)$this->subject;
    }

    public function canDelete(): string|bool
    {
        if ($this->status === Mail::STATUS_SENDING) {
            return 'crud.canDelete.mail';
        }

        return true;
    }

    /**
     * Get % of mails sent, on the total mails to send
     */
    public function getPercentSent(): float
    {
        if (!$this->totalToSend) {
            return 0;
        }

        return round($this->totalSended / $this->totalToSend * 100, 2);
    }

    /**
     * Get % of mails opened by the users, on the total mails to send
     */
    public function getPercentOpened(): float
    {
        if (!$this->totalToSend) {
            return 0;
        }

        return round($this->totalOpened / $this->totalToSend * 100, 2);
    }

    public function getPercentError(): float
    {
        if (!$this->totalToSend) {
            return 0;
        }

        return round($this->totalError / $this->totalToSend * 100, 2);
    }

    public function getPercentUnsubscribed(): float
    {
        if (!$this->totalToSend) {
            return 0;
        }

        return round($this->totalUnsubscribed / $this->totalToSend * 100, 2);
    }

    public function getJsonAttachement(): array
    {
        $json = json_encode($this->attachement);

        return $json ? json_decode($json, true) : [];
    }

    public function getPathOfAttachement(string $file): ?string
    {
        foreach ($this->getJsonAttachement() as $attachement) {
            if ($attachement["name"] === $file) {
                return $attachement["path"] . $file;
            }
        }

        return null;
    }

    public function countRecipients(): int
    {
        $recipients = 0;

        /** @var Recipient $recipient */
        foreach ($this->recipients as $recipient) {
            if (!$recipient->isTest()) {
                $recipients++;
            }
        }

        return $recipients;
    }

    public function getTotalLinkOpening(): int
    {
        $total = 0;
        foreach ($this->links as $link) {
            $total += $link->getTotalOpened();
        }

        return $total;
    }

    public function getTotalLinkOpeningRate(): float
    {
        $openinglinks = 0;
        foreach ($this->links as $link) {
            $openinglinks += $link->getLinkOpenings()->count();
        }

        if ($this->recipients->count() === 0) {
            return 0;
        }

        return round($openinglinks / $this->recipients->count() * 100, 2);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): Mail
    {
        $this->id = $id;

        return $this;
    }

    public function getTemplate(): ?Template
    {
        return $this->template;
    }

    public function setTemplate(?Template $template): Mail
    {
        $this->template = $template;

        return $this;
    }

    public function getSenderName(): ?string
    {
        return $this->senderName;
    }

    public function setSenderName(?string $senderName): Mail
    {
        $this->senderName = $senderName;

        return $this;
    }

    public function getSenderEmail(): ?string
    {
        return $this->senderEmail;
    }

    public function setSenderEmail(?string $senderEmail): Mail
    {
        $this->senderEmail = $senderEmail;

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): Mail
    {
        $this->data = $data;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): Mail
    {
        $this->status = $status;

        return $this;
    }

    public function getTotalToSend(): int
    {
        return $this->totalToSend;
    }

    public function setTotalToSend(int $totalToSend): Mail
    {
        $this->totalToSend = $totalToSend;

        return $this;
    }

    public function getTotalSended(): int
    {
        return $this->totalSended;
    }

    public function setTotalSended(int $totalSended): Mail
    {
        $this->totalSended = $totalSended;

        return $this;
    }

    public function getRecipients(): Collection
    {
        return $this->recipients;
    }

    public function addRecipient(Recipient $recipient): Mail
    {
        $recipient->setMail($this);
        $this->recipients->add($recipient);

        return $this;
    }

    public function removeRecipient(Recipient $recipient): Mail
    {
        if ($this->recipients->contains($recipient)) {
            $this->recipients->removeElement($recipient);
        }

        return $this;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): Mail
    {
        $this->subject = $subject;

        return $this;
    }

    public function getSendingDate(): ?DateTime
    {
        return $this->sendingDate;
    }

    public function setSendingDate(?DateTime $sendingDate): Mail
    {
        $this->sendingDate = $sendingDate;

        return $this;
    }

    public function getSendAtDate(): ?DateTime
    {
        return $this->sendAtDate;
    }

    public function setSendAtDate(?DateTime $sendAtDate): Mail
    {
        $this->sendAtDate = $sendAtDate;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): Mail
    {
        $this->text = $text;

        return $this;
    }

    public function getHtml(): ?string
    {
        return $this->html;
    }

    public function setHtml(?string $html): Mail
    {
        $this->html = $html;

        return $this;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTime $createdAt): Mail
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTime $updatedAt): Mail
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getTotalUnsubscribed(): int
    {
        return $this->totalUnsubscribed;
    }

    public function setTotalUnsubscribed(int $totalUnsubscribed): Mail
    {
        $this->totalUnsubscribed = $totalUnsubscribed;

        return $this;
    }

    public function getTotalError(): int
    {
        return $this->totalError;
    }

    public function setTotalError(int $totalError): Mail
    {
        $this->totalError = $totalError;

        return $this;
    }

    public function getAttachement(): array
    {
        return $this->attachement;
    }

    public function setAttachement(array $attachement): Mail
    {
        $this->attachement = $attachement;

        return $this;
    }

    public function getTotalOpened(): int
    {
        return $this->totalOpened;
    }

    public function setTotalOpened(int $totalOpened): Mail
    {
        $this->totalOpened = $totalOpened;

        return $this;
    }

    public function getCcRecipients(): Collection
    {
        return $this->ccRecipients;
    }

    public function addCcRecipient(Recipient $ccRecipient): Mail
    {
        $ccRecipient->setCcMail($this);
        $this->ccRecipients->add($ccRecipient);

        return $this;
    }

    public function removeCcRecipient(Recipient $ccRecipient): Mail
    {
        if ($this->ccRecipients->contains($ccRecipient)) {
            $this->ccRecipients->removeElement($ccRecipient);
        }

        return $this;
    }

    public function getLinks(): Collection
    {
        return $this->links;
    }

    public function addLink(Link $link): Mail
    {
        if (!$this->links->contains($link)) {
            $link->setMail($this);
            $this->links->add($link);
        }

        return $this;
    }

    public function removeLink(Link $link): Mail
    {
        if ($this->links->contains($link)) {
            $this->links->removeElement($link);
        }

        return $this;
    }

    public function getTenantId(): ?int
    {
        return $this->tenantId;
    }

    public function setTenantId(?int $tenantId): self
    {
        $this->tenantId = $tenantId;

        return $this;
    }

    public function getDsn(): ?string
    {
        return $this->dsn;
    }

    public function setDsn(?string $dsn): self
    {
        $this->dsn = $dsn;

        return $this;
    }

    public function hasAttachmentsDeleted(): bool
    {
        return $this->attachmentsDeleted;
    }

    public function setAttachmentsDeleted(bool $attachmentsDeleted): self
    {
        $this->attachmentsDeleted = $attachmentsDeleted;

        return $this;
    }

    public function getEntityClass(): ?string
    {
        return $this->entityClass;
    }

    public function setEntityClass(?string $entityClass): self
    {
        $this->entityClass = $entityClass;

        return $this;
    }

    public function getEntityId(): ?int
    {
        return $this->entityId;
    }

    public function setEntityId(?int $entityId): self
    {
        $this->entityId = $entityId;

        return $this;
    }

    public function getMjml(): ?string
    {
        return $this->mjml;
    }

    public function setMjml(?string $mjml): void
    {
        $this->mjml = $mjml;
    }
}
