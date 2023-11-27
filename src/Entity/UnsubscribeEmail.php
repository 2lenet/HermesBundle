<?php

namespace Lle\HermesBundle\Entity;

use DateTime;
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
}
