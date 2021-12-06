<?php

namespace Lle\HermesBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Lle\HermesBundle\Repository\EmailErrorRepository;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class EmailError
 * @package Lle\HermesBundle\Entity
 *
 * @author 2LE <2le@2le.net>
 *
 * @ORM\Entity(repositoryClass=EmailErrorRepository::class)
 * @ORM\Table(name="lle_hermes_email_error")
 */
class EmailError
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="integer")
     */
    private int $nbError = 1;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank
     */
    private DateTime $dateError;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(max=255)
     * @Assert\Email
     * @Assert\NotBlank
     */
    private string $email;

    /**
     * @ORM\OneToMany(targetEntity=Error::class, mappedBy="emailError")
     */
    private Collection $errors;

    public function __construct()
    {
        $this->errors = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return EmailError
     */
    public function setId(int $id): EmailError
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getNbError(): int
    {
        return $this->nbError;
    }

    /**
     * @param int $nbError
     * @return EmailError
     */
    public function setNbError(int $nbError): EmailError
    {
        $this->nbError = $nbError;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateError(): DateTime
    {
        return $this->dateError;
    }

    /**
     * @param DateTime $dateError
     * @return EmailError
     */
    public function setDateError(DateTime $dateError): EmailError
    {
        $this->dateError = $dateError;
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
     * @return EmailError
     */
    public function setEmail(string $email): EmailError
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return ArrayCollection|Collection
     */
    public function getErrors()
    {
        return $this->errors;
    }

    public function addError(Error $error): EmailError
    {
        $error->setEmailError($this);
        if (false === $this->errors->contains($error)) {
            $this->errors->add($error);
        }
        return $this;
    }

    public function removeErrror(Error $error): EmailError
    {
        if (true === $this->errors->contains($error)) {
            $this->errors->removeElement($error);
        }
        return $this;
    }
}
