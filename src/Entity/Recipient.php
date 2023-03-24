<?php

namespace Lle\HermesBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Lle\HermesBundle\Repository\RecipientRepository;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Recipient
 * @package Lle\HermesBundle\Entity
 *
 * @author 2LE <2le@2le.net>
 *
 * @ORM\Entity(repositoryClass=RecipientRepository::class)
 * @ORM\Table(name="lle_hermes_recipient")
 */
class Recipient
{
    public const SENDING_STATUS = 'sending';
    public const SENT_STATUS = 'sent';
    public const CANCELLED_STATUS = 'cancelled';
    public const UNSUBSCRIBED_STATUS = 'unsubscribed';
    public const ERROR_STATUS = 'error';

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255)
     */
    protected ?string $toName = null;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(max=255)
     * @Assert\Email
     * @Assert\NotBlank
     */
    protected string $toEmail;

    /**
     * @ORM\Column(type="json")
     */
    protected array $data = [];

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(max=255)
     * @Assert\NotBlank
     */
    protected string $status;

    /**
     * @ORM\Column(type="integer")
     */
    protected int $nbRetry = 0;

    /**
     * @ORM\ManyToOne(targetEntity="Lle\HermesBundle\Entity\Mail", inversedBy="recipients", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    protected ?Mail $mail = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?DateTime $openDate = null;

    /**
     * @ORM\ManyToOne(targetEntity="Lle\HermesBundle\Entity\Mail", inversedBy="ccRecipients", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private ?Mail $ccMail = null;

    /**
     * @ORM\OneToMany(targetEntity="Lle\HermesBundle\Entity\LinkOpening", mappedBy="recipient", cascade={"persist", "remove"})
     */
    protected Collection $linkOpenings;

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

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Recipient
     */
    public function setId(int $id): Recipient
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getToName(): ?string
    {
        return $this->toName;
    }

    /**
     * @param string|null $toName
     * @return Recipient
     */
    public function setToName(?string $toName): Recipient
    {
        $this->toName = $toName;
        return $this;
    }

    /**
     * @return string
     */
    public function getToEmail(): string
    {
        return $this->toEmail;
    }

    /**
     * @param string $toEmail
     * @return Recipient
     */
    public function setToEmail(string $toEmail): Recipient
    {
        $this->toEmail = $toEmail;
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
     * @return Recipient
     */
    public function setData(array $data): Recipient
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
     * @return Recipient
     */
    public function setStatus(string $status): Recipient
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return int
     */
    public function getNbRetry(): int
    {
        return $this->nbRetry;
    }

    /**
     * @param int $nbRetry
     * @return Recipient
     */
    public function setNbRetry(int $nbRetry): Recipient
    {
        $this->nbRetry = $nbRetry;
        return $this;
    }

    /**
     * @return Mail
     */
    public function getMail(): ?Mail
    {
        return $this->mail;
    }

    /**
     * @param Mail $mail
     * @return Recipient
     */
    public function setMail(?Mail $mail): Recipient
    {
        $this->mail = $mail;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getOpenDate(): ?DateTime
    {
        return $this->openDate;
    }

    /**
     * @param DateTime|null $openDate
     * @return Recipient
     */
    public function setOpenDate(?DateTime $openDate): Recipient
    {
        $this->openDate = $openDate;
        return $this;
    }

    /**
     * @return Mail
     */
    public function getCcMail(): ?Mail
    {
        return $this->ccMail;
    }

    /**
     * @param Mail $ccMail
     * @return Recipient
     */
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
}
