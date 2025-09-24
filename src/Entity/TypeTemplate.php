<?php

namespace Lle\HermesBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Lle\CruditBundle\Contracts\CruditEntityInterface;
use Lle\HermesBundle\Repository\TypeTemplateRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TypeTemplateRepository::class)]
#[ORM\Table(name: 'lle_hermes_type_template')]
class TypeTemplate implements CruditEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $label = null;

    #[ORM\Column]
    private ?bool $unsubscriptionsAllowed = null;

    /**
     * @var Collection<int, UnsubscribeEmail>
     */
    #[ORM\ManyToMany(targetEntity: UnsubscribeEmail::class, mappedBy: 'typesTemplate', fetch: 'EXTRA_LAZY', cascade: ['remove'])]
    private Collection $unsubscribedEmails;

    /**
     * @var Collection<int, Template>
     */
    #[ORM\OneToMany(targetEntity: Template::class, mappedBy: 'typeTemplate')]
    protected Collection $templates;

    public function __construct()
    {
        $this->unsubscribedEmails = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string)$this->label;
    }

    public function canEdit(): bool
    {
        if (!$this->templates->isEmpty()) {
            return 'type_template.can_delete.templates';
        }

        return true;
    }

    public function canDelete(): bool|string
    {
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

    public function isUnsubscriptionsAllowed(): ?bool
    {
        return $this->unsubscriptionsAllowed;
    }

    public function setUnsubscriptionsAllowed(bool $unsubscriptionsAllowed): self
    {
        $this->unsubscriptionsAllowed = $unsubscriptionsAllowed;

        return $this;
    }

    /**
     * @return Collection<int, UnsubscribeEmail>
     */
    public function getUnsubscribedEmails(): Collection
    {
        return $this->unsubscribedEmails;
    }

    public function addUnsubscribedEmail(UnsubscribeEmail $unsubscribedEmail): self
    {
        if (!$this->unsubscribedEmails->contains($unsubscribedEmail)) {
            $this->unsubscribedEmails->add($unsubscribedEmail);
            $unsubscribedEmail->addTypeTemplate($this);
        }

        return $this;
    }

    public function removeUnsubscribedEmail(UnsubscribeEmail $unsubscribedEmail): self
    {
        if ($this->unsubscribedEmails->removeElement($unsubscribedEmail)) {
            $unsubscribedEmail->removeTypeTemplate($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Template>
     */
    public function getTemplates(): Collection
    {
        return $this->templates;
    }

    public function addTemplate(Template $template): self
    {
        if (!$this->templates->contains($template)) {
            $this->templates->add($template);
            $template->setTypeTemplate($this);
        }

        return $this;
    }

    public function removeTemplate(Template $template): self
    {
        if ($this->templates->removeElement($template)) {
            // set the owning side to null (unless already changed)
            if ($template->getTypeTemplate() === $this) {
                $template->setTypeTemplate(null);
            }
        }

        return $this;
    }
}
