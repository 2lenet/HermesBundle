<?php

namespace Lle\HermesBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Lle\HermesBundle\Repository\ErrorRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ErrorRepository::class)]
#[ORM\Table(name: 'lle_hermes_error')]
class Error
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotBlank]
    private DateTime $date;

    #[ORM\Column(type: 'string', length: 1024)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 1024)]
    private string $subject;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content = null;

    #[ORM\ManyToOne(targetEntity: EmailError::class, inversedBy: 'errors')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private EmailError $emailError;

    public function __toString(): string
    {
        return $this->subject;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): Error
    {
        $this->id = $id;

        return $this;
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }

    public function setDate(DateTime $date): Error
    {
        $this->date = $date;

        return $this;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): Error
    {
        $this->subject = $subject;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): Error
    {
        $this->content = $content;

        return $this;
    }

    public function getEmailError(): EmailError
    {
        return $this->emailError;
    }

    public function setEmailError(EmailError $emailError): Error
    {
        $this->emailError = $emailError;

        return $this;
    }
}
