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
 *
 * @ORM\Entity(repositoryClass=TemplateRepository::class)
 * @ORM\Table(name="lle_hermes_template")
 */
class Template
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
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    protected string $libelle;

    /**
     * @ORM\Column(type="string", length=1024)
     * @Assert\NotBlank
     * @Assert\Length(max=1024)
     */
    protected string $subject;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255)
     */
    protected ?string $senderName = null;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     * @Assert\Email
     */
    protected string $senderEmail;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $mjml = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $text = null;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    protected string $code;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $html = null;

    /**
     * @ORM\Column(type="boolean")
     */
    protected bool $unsubscriptions = true;

    /**
     * @ORM\Column(type="boolean")
     */
    protected bool $statistics = true;

    public function __toString(): string
    {
        return sprintf('%s %s', $this->code, $this->getSubject());
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
     * @return Template
     */
    public function setId(int $id): Template
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getLibelle(): string
    {
        return $this->libelle;
    }

    /**
     * @param string $libelle
     * @return Template
     */
    public function setLibelle(string $libelle): Template
    {
        $this->libelle = $libelle;
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
     * @return Template
     */
    public function setSubject(string $subject): Template
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSenderName(): ?string
    {
        return $this->senderName;
    }

    /**
     * @param string|null $senderName
     * @return Template
     */
    public function setSenderName(?string $senderName): Template
    {
        $this->senderName = $senderName;
        return $this;
    }

    /**
     * @return string
     */
    public function getSenderEmail(): string
    {
        return $this->senderEmail;
    }

    /**
     * @param string $senderEmail
     * @return Template
     */
    public function setSenderEmail(string $senderEmail): Template
    {
        $this->senderEmail = $senderEmail;
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
     * @return Template
     */
    public function setMjml(?string $mjml): Template
    {
        $this->mjml = $mjml;
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
     * @return Template
     */
    public function setText(?string $text): Template
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return Template
     */
    public function setCode(string $code): Template
    {
        $this->code = $code;
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
     * @return Template
     */
    public function setHtml(?string $html): Template
    {
        $this->html = $html;
        return $this;
    }

    /**
     * @return bool
     */
    public function isUnsubscriptions(): bool
    {
        return $this->unsubscriptions;
    }

    /**
     * @param bool $unsubscriptions
     * @return Template
     */
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
