<?php

namespace Lle\HermesBundle\Entity;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Lle\HermesBundle\Repository\RecipientRepository;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Recipient
 * @package Lle\HermesBundle\Entity
 *
 * @author 2LE <2le@2le.net>
 *
 * @ORM\Entity(repositoryClass=Recipient::class)
 * @ORM\Table(name="lle_hermes_recipient")
 */
#[ORM\Entity(repositoryClass: RecipientRepository::class)]
#[ORM\Table(name: 'lle_hermes_recipient')]
class Recipient
{

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255)
     */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    protected ?string $toName = null;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(max=255)
     */
    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank()]
    protected string $toEmail;

    /**
     * @ORM\Column(type="json")
     */
    #[ORM\Column(type: Types::JSON)]
    #[Assert\Json()]
    #[Assert\NotBlank()]
    protected array $data = [];

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(max=255)
     */
    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank()]
    protected string $status;

    /**
     * @ORM\Column(type="integer")
     */
    #[ORM\Column(type: Types::INTEGER)]
    protected int $nbRetry = 0;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Mail", inversedBy="recipient" ,cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    #[ORM\ManyToOne(targetEntity: Mail::class, inversedBy: 'recipient', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank()]
    protected Mail $mail;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?DateTime $opentDate = null;

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
    public function getMail(): Mail
    {
        return $this->mail;
    }

    /**
     * @param Mail $mail
     * @return Recipient
     */
    public function setMail(Mail $mail): Recipient
    {
        $this->mail = $mail;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getOpentDate(): ?DateTime
    {
        return $this->opentDate;
    }

    /**
     * @param DateTime|null $opentDate
     * @return Recipient
     */
    public function setOpentDate(?DateTime $opentDate): Recipient
    {
        $this->opentDate = $opentDate;
        return $this;
    }
}
