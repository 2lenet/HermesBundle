<?php

namespace Lle\HermesBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Lle\HermesBundle\Repository\UnsubscribeEmailRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UnsubscribeEmailRepository::class)]
#[ORM\Table(name: 'lle_hermes_unsubscribe_email')]
class UnsubscribeEmail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private string $email;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotBlank]
    private DateTime $unsubscribeDate;

    /**
     * @var Collection<int, TypeTemplate>
     */
    #[ORM\ManyToMany(targetEntity: TypeTemplate::class, inversedBy: 'unsubscribedEmails')]
    #[ORM\JoinTable(name: 'lle_hermes_unsubscribe_email_type_template')]
    private Collection $typesTemplate;

    public function __construct()
    {
        $this->typesTemplate = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->email;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): UnsubscribeEmail
    {
        $this->id = $id;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): UnsubscribeEmail
    {
        $this->email = $email;

        return $this;
    }

    public function getUnsubscribeDate(): DateTime
    {
        return $this->unsubscribeDate;
    }

    public function setUnsubscribeDate(DateTime $unsubscribeDate): UnsubscribeEmail
    {
        $this->unsubscribeDate = $unsubscribeDate;

        return $this;
    }

    /**
     * @return Collection<int, TypeTemplate>
     */
    public function getTypesTemplate(): Collection
    {
        return $this->typesTemplate;
    }

    public function addTypeTemplate(TypeTemplate $typeTemplate): self
    {
        if (!$this->typesTemplate->contains($typeTemplate)) {
            $this->typesTemplate->add($typeTemplate);
        }

        return $this;
    }

    public function removeTypeTemplate(TypeTemplate $typeTemplate): self
    {
        $this->typesTemplate->removeElement($typeTemplate);

        return $this;
    }
}
