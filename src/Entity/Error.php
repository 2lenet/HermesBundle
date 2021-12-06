<?php

namespace Lle\HermesBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Lle\HermesBundle\Repository\ErrorRepository;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Error
 * @package Lle\HermesBundle\Entity
 *
 * @author 2LE <2le@2le.net>
 *
 * @ORM\Entity(repositoryClass=ErrorRepository::class)
 * @ORM\Table(name="lle_hermes_error")
 */
class Error
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank
     */
    private DateTime $date;

    /**
     * @ORM\Column(type="string", length=1024)
     * @Assert\NotBlank
     * @Assert\Length(max=1024)
     */
    private string $subject;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank
     */
    private string $content;

    /**
     * @ORM\ManyToOne(targetEntity=EmailError::class, inversedBy="errors")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank
     */
    private EmailError $emailError;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Error
     */
    public function setId(int $id): Error
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * @param DateTime $date
     * @return Error
     */
    public function setDate(DateTime $date): Error
    {
        $this->date = $date;
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
     * @return Error
     */
    public function setSubject(string $subject): Error
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     * @return Error
     */
    public function setContent(string $content): Error
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return EmailError
     */
    public function getEmailError(): EmailError
    {
        return $this->emailError;
    }

    /**
     * @param EmailError $emailError
     * @return Error
     */
    public function setEmailError(EmailError $emailError): Error
    {
        $this->emailError = $emailError;
        return $this;
    }
}
