<?php

namespace Lle\HermesBundle\Entity;

use DateTime;
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
     * @ORM\ManyToOne(targetEntity="Lle\HermesBundle\Entity\Mail", inversedBy="recipient" ,cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    protected Mail $mail;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?DateTime $openDate = null;

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
}
