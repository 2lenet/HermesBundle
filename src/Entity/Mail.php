<?php

namespace Lle\HermesBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Lle\HermesBundle\Repository\MailRepository;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Mail
 * @package Lle\HermesBundle\Entity
 *
 * @author 2LE <2le@2le.net>
 *
 * @ORM\Entity(repositoryClass=MailRepository::class)
 * @ORM\Table(name="lle_hermes_mail")
 */
class Mail
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Lle\HermesBundle\Entity\Template")
     * @Assert\NotBlank
     */
    protected Template $template;

    /**
     * @ORM\Column(type="json", nullable=false)
     */
    protected array $data = [];

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    protected string $status;

    /**
     * @ORM\Column(type="integer")
     */
    protected int $totalToSend = 0;

    /**
     * @ORM\Column(type="integer")
     */
    protected int $totalSended = 0;

    /**
     * @ORM\OneToMany(targetEntity="Lle\HermesBundle\Entity\Recipient", mappedBy="mail", cascade={"persist", "remove"})
     */
    protected Collection $recipients;

    /**
     * @ORM\Column(type="string", length=1024)
     * @Assert\NotBlank
     * @Assert\Length(max=1024)
     */
    protected string $subject;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $mjml = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?DateTime $sendingDate = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $text = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $html = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?DateTime $createdAt = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?DateTime $updatedAt = null;

    /**
     * @ORM\Column(type="integer")
     */
    protected int $totalUnsubscribed = 0;

    /**
     * @ORM\Column(type="integer")
     */
    protected int $totalError = 0;

    /**
     * @ORM\Column(type="json")
     */
    protected array $attachement = [];

    /**
     * @ORM\Column(type="integer")
     */
    protected int $totalOpened = 0;

    public function __construct()
    {
        $this->recipients = new ArrayCollection();
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
     * @return Mail
     */
    public function setId(int $id): Mail
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param mixed $template
     * @return Mail
     */
    public function setTemplate($template)
    {
        $this->template = $template;
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
     * @return Mail
     */
    public function setData(array $data): Mail
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
     * @return Mail
     */
    public function setStatus(string $status): Mail
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalToSend(): int
    {
        return $this->totalToSend;
    }

    /**
     * @param int $totalToSend
     * @return Mail
     */
    public function setTotalToSend(int $totalToSend): Mail
    {
        $this->totalToSend = $totalToSend;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalSended(): int
    {
        return $this->totalSended;
    }

    /**
     * @param int $totalSended
     * @return Mail
     */
    public function setTotalSended(int $totalSended): Mail
    {
        $this->totalSended = $totalSended;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getRecipients(): Collection
    {
        return $this->recipients;
    }

    /**
     * @param Recipient $recipient
     * @return $this
     */
    public function addRecipient(Recipient $recipient): Mail
    {
        $recipient->setMail($this);
        $this->recipients->add($recipient);
        return $this;
    }

    /**
     * @param Recipient $recipient
     * @return $this
     */
    public function removeRecipient(Recipient $recipient): Mail
    {
        if ($this->recipients->contains($recipient)) {
            $this->recipients->removeElement($recipient);
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     * @return Mail
     */
    public function setSubject(string $subject): Mail
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMjml(): ?string
    {
        return $this->mjml;
    }

    /**
     * @param string|null $mjml
     * @return Mail
     */
    public function setMjml(?string $mjml): Mail
    {
        $this->mjml = $mjml;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getSendingDate(): ?DateTime
    {
        return $this->sendingDate;
    }

    /**
     * @param DateTime|null $sendingDate
     * @return Mail
     */
    public function setSendingDate(?DateTime $sendingDate): Mail
    {
        $this->sendingDate = $sendingDate;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * @param string|null $text
     * @return Mail
     */
    public function setText(?string $text): Mail
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHtml(): ?string
    {
        return $this->html;
    }

    /**
     * @param string|null $html
     * @return Mail
     */
    public function setHtml(?string $html): Mail
    {
        $this->html = $html;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime|null $createdAt
     * @return Mail
     */
    public function setCreatedAt(?DateTime $createdAt): Mail
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param DateTime|null $updatedAt
     * @return Mail
     */
    public function setUpdatedAt(?DateTime $updatedAt): Mail
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalUnsubscribed(): int
    {
        return $this->totalUnsubscribed;
    }

    /**
     * @param int $totalUnsubscribed
     * @return Mail
     */
    public function setTotalUnsubscribed(int $totalUnsubscribed): Mail
    {
        $this->totalUnsubscribed = $totalUnsubscribed;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalError(): int
    {
        return $this->totalError;
    }

    /**
     * @param int $totalError
     * @return Mail
     */
    public function setTotalError(int $totalError): Mail
    {
        $this->totalError = $totalError;
        return $this;
    }

    /**
     * @return array
     */
    public function getAttachement(): array
    {
        return $this->attachement;
    }

    /**
     * @param array $attachement
     * @return Mail
     */
    public function setAttachement(array $attachement): Mail
    {
        $this->attachement = $attachement;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalOpened(): int
    {
        return $this->totalOpened;
    }

    /**
     * @param int $totalOpened
     * @return Mail
     */
    public function setTotalOpened(int $totalOpened): Mail
    {
        $this->totalOpened = $totalOpened;
        return $this;
    }
}
