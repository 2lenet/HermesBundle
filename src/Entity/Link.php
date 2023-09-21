<?php

namespace Lle\HermesBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Lle\HermesBundle\Repository\LinkRepository;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Link
 * @package Lle\HermesBundle\Entity
 *
 * @author 2LE <2le@2le.net>
 *
 * @ORM\Entity(repositoryClass=LinkRepository::class)
 * @ORM\Table(name="lle_hermes_link")
 */
class Link
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected ?int $id;
    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    protected string $url;
    /**
     * @ORM\ManyToOne(targetEntity="Lle\HermesBundle\Entity\Mail", inversedBy="links", cascade={"persist"})
     * @Assert\NotBlank
     */
    protected Mail $mail;
    /**
     * @ORM\OneToMany(targetEntity="Lle\HermesBundle\Entity\LinkOpening", mappedBy="link", cascade={"persist", "remove"})
     */
    protected Collection $linkOpenings;

    public function __construct()
    {
        $this->linkOpenings = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->url;
    }

    public function getTotalOpened(): int
    {
        $total = 0;
        foreach ($this->getLinkOpenings() as $linkOpening) {
            $total += $linkOpening->getNbOpenings();
        }

        return $total;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getMail(): Mail
    {
        return $this->mail;
    }

    public function setMail(Mail $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    public function getLinkOpenings(): Collection
    {
        return $this->linkOpenings;
    }

    public function addLinkOpening(LinkOpening $linkOpening): self
    {
        if (!$this->linkOpenings->contains($linkOpening)) {
            $linkOpening->setLink($this);
            $this->linkOpenings->add($linkOpening);
        }

        return $this;
    }

    public function removeLinkOpening(LinkOpening $linkOpening): self
    {
        if ($this->linkOpenings->contains($linkOpening)) {
            $this->linkOpenings->removeElement($linkOpening);
        }

        return $this;
    }
}
