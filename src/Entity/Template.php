<?php

namespace Lle\HermesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Lle\HermesBundle\Repository\TemplateRepository;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Template
 * @package Lle\HermesBundle\Entity
 *
 * @author 2LE <2le@2le.net>
 */
#[ORM\Entity(repositoryClass: TemplateRepository::class)]
#[ORM\Table(name: 'lle_hermes_template')]
class Template
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    protected string $libelle;

    #[ORM\Column(type: 'string', length: 1024)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 1024)]
    protected string $subject;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    protected ?string $senderName = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Assert\Email]
    protected string $senderEmail;

    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $text = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    protected string $code;

    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $html = null;

    #[ORM\Column(type: 'boolean')]
    protected bool $unsubscriptions = false;

    #[ORM\Column(type: 'boolean')]
    protected bool $statistics = false;

    public function __toString(): string
    {
        return sprintf('%s %s', $this->code, $this->getSubject());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): Template
    {
        $this->id = $id;

        return $this;
    }

    public function getLibelle(): string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): Template
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): Template
    {
        $this->subject = $subject;

        return $this;
    }

    public function getSenderName(): ?string
    {
        return $this->senderName;
    }

    public function setSenderName(?string $senderName): Template
    {
        $this->senderName = $senderName;

        return $this;
    }

    public function getSenderEmail(): string
    {
        return $this->senderEmail;
    }

    public function setSenderEmail(string $senderEmail): Template
    {
        $this->senderEmail = $senderEmail;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): Template
    {
        $this->text = $text;

        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): Template
    {
        $this->code = $code;

        return $this;
    }

    public function getHtml(): ?string
    {
        return $this->html;
    }

    public function setHtml(?string $html): Template
    {
        $this->html = $html;

        return $this;
    }

    public function isUnsubscriptions(): bool
    {
        return $this->unsubscriptions;
    }

    public function setUnsubscriptions(bool $unsubscriptions): Template
    {
        $this->unsubscriptions = $unsubscriptions;

        return $this;
    }

    public function hasStatistics(): bool
    {
        return $this->statistics;
    }

    public function setStatistics(bool $statistics): Template
    {
        $this->statistics = $statistics;

        return $this;
    }
}
