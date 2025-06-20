<?php

namespace Lle\HermesBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Lle\HermesBundle\Contracts\MultiTenantInterface;
use Lle\HermesBundle\Repository\RecipientRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RecipientRepository::class)]
#[ORM\Table(name: 'lle_hermes_recipient')]
#[ORM\Index(name: 'tenant_id_idx', columns: ['tenant_id'])]
class Recipient implements MultiTenantInterface
{
    public const STATUS_SENDING = 'sending';
    public const STATUS_SENT = 'sent';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_UNSUBSCRIBED = 'unsubscribed';
    public const STATUS_ERROR = 'error';

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    protected ?string $toName = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\Length(max: 255)]
    #[Assert\Email]
    #[Assert\NotBlank]
    protected string $toEmail;

    #[ORM\Column(type: 'json')]
    protected array $data = [];

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    protected string $status;

    #[ORM\ManyToOne(targetEntity: Mail::class, inversedBy: 'recipients', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: true)]
    protected ?Mail $mail = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?DateTime $openDate = null;

    #[ORM\ManyToOne(targetEntity: Mail::class, inversedBy: 'ccRecipients', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: true)]
    protected ?Mail $ccMail = null;

    #[ORM\OneToMany(targetEntity: LinkOpening::class, mappedBy: 'recipient', cascade: ['persist', 'remove'])]
    protected Collection $linkOpenings;

    #[ORM\Column(type: 'boolean')]
    protected bool $test = false;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected ?int $tenantId = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    protected string $errorMessage;


    public function __construct()
    {
        $this->linkOpenings = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string)$this->toEmail;
    }

    public function getTotalLinkOpening(): int
    {
        $total = 0;

        foreach ($this->linkOpenings as $linkOpening) {
            $total += $linkOpening->getNbOpenings();
        }

        return $total;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): Recipient
    {
        $this->id = $id;

        return $this;
    }

    public function getToName(): ?string
    {
        return $this->toName;
    }

    public function setToName(?string $toName): Recipient
    {
        $this->toName = $toName;

        return $this;
    }

    public function getToEmail(): string
    {
        return $this->toEmail;
    }

    public function setToEmail(string $toEmail): Recipient
    {
        $this->toEmail = $toEmail;

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): Recipient
    {
        $this->data = $data;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): Recipient
    {
        $this->status = $status;

        return $this;
    }

    public function getMail(): ?Mail
    {
        return $this->mail;
    }

    public function setMail(?Mail $mail): Recipient
    {
        $this->mail = $mail;

        return $this;
    }

    public function getOpenDate(): ?DateTime
    {
        return $this->openDate;
    }

    public function setOpenDate(?DateTime $openDate): Recipient
    {
        $this->openDate = $openDate;

        return $this;
    }

    public function getCcMail(): ?Mail
    {
        return $this->ccMail;
    }

    public function setCcMail(?Mail $ccMail): Recipient
    {
        $this->ccMail = $ccMail;

        return $this;
    }

    public function getLinkOpenings(): Collection
    {
        return $this->linkOpenings;
    }

    public function addLinkOpening(LinkOpening $linkOpening): self
    {
        if (!$this->linkOpenings->contains($linkOpening)) {
            $linkOpening->setRecipient($this);
            $this->linkOpenings->add($linkOpening);
        }

        return $this;
    }

    public function removeLinkOpening(LinkOpening $linkOpening): self
    {
        if ($this->linkOpenings->contains($linkOpening)) {
            $this->linkOpenings->removeElement($linkOpening);
        }

        return $this;
    }

    public function isTest(): bool
    {
        return $this->test;
    }

    public function setTest(bool $test): Recipient
    {
        $this->test = $test;

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

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): self
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }
}
