<?php

namespace Lle\HermesBundle\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Lle\HermesBundle\Repository\LinkOpeningRepository;

/**
 * Class LinkOpening
 * @package Lle\HermesBundle\Entity
 *
 * @author 2LE <2le@2le.net>
 *
 * @ORM\Entity(repositoryClass=LinkOpeningRepository::class)
 * @ORM\Table(name="lle_hermes_link_opening")
 */
class LinkOpening
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected ?int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Lle\HermesBundle\Entity\Link", inversedBy="linkOpenings", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    protected Link $link;

    /**
     * @ORM\ManyToOne(targetEntity="Lle\HermesBundle\Entity\Recipient", inversedBy="linkOpenings", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    protected Recipient $recipient;

    /**
     * @ORM\Column(type="integer")
     */
    protected int $nbOpenings = 0;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?DateTimeInterface $createdAt = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?DateTimeInterface $updatedAt = null;

    public function __toString(): string
    {
        return $this->url;
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

    public function getLink(): Link
    {
        return $this->link;
    }

    public function setLink(Link $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function getRecipient(): Recipient
    {
        return $this->recipient;
    }

    public function setRecipient(Recipient $recipient): self
    {
        $this->recipient = $recipient;

        return $this;
    }

    public function getNbOpenings(): int
    {
        return $this->nbOpenings;
    }

    public function setNbOpenings(int $nbOpenings): self
    {
        $this->nbOpenings = $nbOpenings;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
