<?php

namespace Lle\HermesBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Lle\HermesBundle\Repository\ConsentRepository;

#[ORM\Entity(repositoryClass: ConsentRepository::class)]
#[ORM\Table(name: 'lle_hermes_consent')]
class Consent
{
    public const string TYPE_PIXEL = 'PIXEL';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 255, options: ['default' => self::TYPE_PIXEL])]
    private ?string $type = self::TYPE_PIXEL;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $datetime = null;

    #[ORM\Column(type: 'boolean')]
    private bool $value = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getDatetime(): ?DateTime
    {
        return $this->datetime;
    }

    public function setDatetime(?DateTime $datetime): void
    {
        $this->datetime = $datetime;
    }

    public function isValue(): bool
    {
        return $this->value;
    }

    public function setValue(bool $value): void
    {
        $this->value = $value;
    }
}
