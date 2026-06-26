<?php

namespace Lle\HermesBundle\Translatable;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Mapping\Annotation\Translatable;
use Lle\HermesBundle\Contracts\TemplateInterface;
use Lle\HermesBundle\Repository\TranslatableTemplateRepository;
use Lle\HermesBundle\Translatable\Translation\TemplateTranslation;
use Lle\HermesBundle\Validator as HermesAssert;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TranslatableTemplateRepository::class)]
#[Gedmo\TranslationEntity(class: TemplateTranslation::class)]
#[ORM\Table(name: 'lle_hermes_template')]
#[ORM\Index(name: 'tenant_id_idx', columns: ['tenant_id'])]
class TranslatableTemplate implements TemplateInterface
{
    public const string TYPE_HTML = 'html';

    public const string TYPE_CKEDITOR = 'ckeditor';

    public const string TYPE_MJML = 'mjml';

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Translatable]
    #[HermesAssert\RequiredField]
    #[Assert\Length(max: 255)]
    protected ?string $libelle = null;

    #[ORM\Column(type: 'string', length: 1024, nullable: true)]
    #[Translatable]
    #[HermesAssert\RequiredField]
    #[Assert\Length(max: 1024)]
    protected ?string $subject = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Translatable]
    #[Assert\Length(max: 255)]
    protected ?string $senderName = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Translatable]
    #[HermesAssert\RequiredField]
    #[Assert\Length(max: 255)]
    #[Assert\Email]
    protected ?string $senderEmail = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Translatable]
    #[HermesAssert\TwigSyntax]
    protected ?string $text = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    protected ?string $code = null;

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

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getSenderName(): ?string
    {
        return $this->senderName;
    }

    public function setSenderName(?string $senderName): static
    {
        $this->senderName = $senderName;

        return $this;
    }

    public function getSenderEmail(): ?string
    {
        return $this->senderEmail;
    }

    public function setSenderEmail(string $senderEmail): static
    {
        $this->senderEmail = $senderEmail;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getHtml(): ?string
    {
        return $this->html;
    }

    public function setHtml(?string $html): static
    {
        $this->html = $html;

        return $this;
    }

    public function isUnsubscriptions(): bool
    {
        return $this->unsubscriptions;
    }

    public function setUnsubscriptions(bool $unsubscriptions): static
    {
        $this->unsubscriptions = $unsubscriptions;

        return $this;
    }

    public function hasStatistics(): bool
    {
        return $this->statistics;
    }

    public function setStatistics(bool $statistics): static
    {
        $this->statistics = $statistics;

        return $this;
    }

    public function hasSendToErrors(): bool
    {
        return $this->sendToErrors;
    }

    public function setSendToErrors(bool $sendToErrors): static
    {
        $this->sendToErrors = $sendToErrors;

        return $this;
    }

    public function getTenantId(): ?int
    {
        return $this->tenantId;
    }

    public function setTenantId(?int $tenantId): static
    {
        $this->tenantId = $tenantId;

        return $this;
    }

    public function getCustomBounceEmail(): ?string
    {
        return $this->customBounceEmail;
    }

    public function setCustomBounceEmail(?string $customBounceEmail): static
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

    public function removeTranslation(TemplateTranslation $templateTranslation): void
    {
        if ($this->translations->contains($templateTranslation)) {
            $this->translations->removeElement($templateTranslation);
        }
    }
}
