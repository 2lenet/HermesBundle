<?php

namespace Lle\HermesBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Lle\HermesBundle\Contracts\TemplateInterface;
use Lle\HermesBundle\Repository\TypeTemplateRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TypeTemplateRepository::class)]
#[ORM\Table(name: 'lle_hermes_type_template')]
#[UniqueEntity(fields: ['code'])]
class TypeTemplate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $code = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $label = null;

    /**
     * Whether recipients can unsubscribe from this type on the public unsubscribe page.
     */
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $unsubscriptionsAllowed = true;

    /**
     * @var Collection<int, TemplateInterface>
     */
    #[ORM\OneToMany(mappedBy: 'typeTemplate', targetEntity: TemplateInterface::class)]
    private Collection $templates;

    public function __construct()
    {
        $this->templates = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string)$this->label;
    }

    public function canDelete(): string|bool
    {
        if (!$this->templates->isEmpty()) {
            return 'crud.canDelete.type_template';
        }

        return true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function isUnsubscriptionsAllowed(): bool
    {
        return $this->unsubscriptionsAllowed;
    }

    public function setUnsubscriptionsAllowed(bool $unsubscriptionsAllowed): self
    {
        $this->unsubscriptionsAllowed = $unsubscriptionsAllowed;

        return $this;
    }

    /**
     * @return Collection<int, TemplateInterface>
     */
    public function getTemplates(): Collection
    {
        return $this->templates;
    }
}
