<?php

namespace Lle\HermesBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Lle\HermesBundle\Repository\UnsubscribeEmailRepository;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UnsubscribeEmailRepository::class)
 * @ORM\Table(name="lle_hermes_unsubscribe_email")
 */
class UnsubscribeEmail
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;
    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(max=255)
     * @Assert\NotBlank
     * @Assert\Email()
     */
    private string $email;
    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank
     */
    private DateTime $unsubscribeDate;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return UnsubscribeEmail
     */
    public function setId(int $id): UnsubscribeEmail
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return UnsubscribeEmail
     */
    public function setEmail(string $email): UnsubscribeEmail
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getUnsubscribeDate(): DateTime
    {
        return $this->unsubscribeDate;
    }

    /**
     * @param DateTime $unsubscribeDate
     * @return UnsubscribeEmail
     */
    public function setUnsubscribeDate(DateTime $unsubscribeDate): UnsubscribeEmail
    {
        $this->unsubscribeDate = $unsubscribeDate;

        return $this;
    }
}
