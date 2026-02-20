<?php

namespace Lle\HermesBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Lle\HermesBundle\Contracts\MultiTenantInterface;
use Lle\HermesBundle\Entity\Translation\TemplateTranslation;
use Lle\HermesBundle\Repository\TemplateRepository;
use Lle\HermesBundle\Validator as HermesAssert;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Mapping\Annotation\Translatable;

#[ORM\Entity(repositoryClass: TemplateRepository::class)]
#[Gedmo\TranslationEntity(class: TemplateTranslation::class)]
#[ORM\Table(name: 'lle_hermes_template')]
#[ORM\Index(name: 'tenant_id_idx', columns: ['tenant_id'])]
class Template implements MultiTenantInterface
{
    public const string TYPE_HTML = 'html';

    public const string TYPE_CKEDITOR = 'ckeditor';

    public const string TYPE_MJML = 'mjml';

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Translatable]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    protected string $libelle;

    #[ORM\Column(type: 'string', length: 1024)]
    #[Translatable]
    #[Assert\NotBlank]
    #[Assert\Length(max: 1024)]
    protected string $subject;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Translatable]
    #[Assert\Length(max: 255)]
    protected ?string $senderName = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Translatable]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Assert\Email]
    protected string $senderEmail;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Translatable]
    #[HermesAssert\TwigSyntax]
    protected ?string $text = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    protected string $code;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Translatable]
    #[HermesAssert\TwigSyntax]
    protected ?string $html = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    protected bool $unsubscriptions = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    protected bool $statistics = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    protected bool $sendToErrors = false;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected ?int $tenantId = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Email]
    protected ?string $customBounceEmail = null;

    #[ORM\Column(type: 'string', options: ['default' => self::TYPE_HTML])]
    protected string $type = self::TYPE_HTML;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Translatable]
    protected ?string $mjml = null;

    #[ORM\OneToMany(mappedBy: 'object', targetEntity: TemplateTranslation::class, cascade: ['persist', 'remove'])]
    private Collection $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

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

    public function hasSendToErrors(): bool
    {
        return $this->sendToErrors;
    }

    public function setSendToErrors(bool $sendToErrors): Template
    {
        $this->sendToErrors = $sendToErrors;

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

    public function getCustomBounceEmail(): ?string
    {
        return $this->customBounceEmail;
    }

    public function setCustomBounceEmail(?string $customBounceEmail): self
    {
        $this->customBounceEmail = $customBounceEmail;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getMjml(): ?string
    {
        return $this->mjml;
    }

    public function setMjml(?string $mjml): void
    {
        $this->mjml = $mjml;
    }

    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(TemplateTranslation $templateTranslation): void
    {
        if (!$this->translations->contains($templateTranslation)) {
            $this->translations[] = $templateTranslation;
            $templateTranslation->setObject($this);
        }
    }

    public function getLibelleFromLocale(string $locale): string
    {
        foreach ($this->getTranslations() as $translation) {
            if ($translation->getLocale() === $locale && $translation->getField() === 'libelle') {
                return $translation->getContent();
            }
        }

        return $this->getLibelle();
    }

    public function getSubjectFromLocale(string $locale): string
    {
        foreach ($this->getTranslations() as $translation) {
            if ($translation->getLocale() === $locale && $translation->getField() === 'subject') {
                return $translation->getContent();
            }
        }

        return $this->getSubject();
    }

    public function getSenderNameFromLocale(string $locale): string
    {
        foreach ($this->getTranslations() as $translation) {
            if ($translation->getLocale() === $locale && $translation->getField() === 'senderName') {
                return $translation->getContent();
            }
        }

        return $this->getSenderName();
    }

    public function getSenderEmailFromLocale(string $locale): string
    {
        foreach ($this->getTranslations() as $translation) {
            if ($translation->getLocale() === $locale && $translation->getField() === 'senderEmail') {
                return $translation->getContent();
            }
        }

        return $this->getSenderEmail();
    }

    public function getTextFromLocale(string $locale): string
    {
        foreach ($this->getTranslations() as $translation) {
            if ($translation->getLocale() === $locale && $translation->getField() === 'text') {
                return $translation->getContent();
            }
        }

        return $this->getText();
    }

    public function getHtmlFromLocale(string $locale): string
    {
        foreach ($this->getTranslations() as $translation) {
            if ($translation->getLocale() === $locale && $translation->getField() === 'html') {
                return $translation->getContent();
            }
        }

        return $this->getHtml();
    }

    public function getMjmlFromLocale(string $locale): string
    {
        foreach ($this->getTranslations() as $translation) {
            if ($translation->getLocale() === $locale && $translation->getField() === 'mjml') {
                return $translation->getContent();
            }
        }

        return $this->getMjml();
    }
}
