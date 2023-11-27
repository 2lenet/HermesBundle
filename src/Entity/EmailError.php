<?php

namespace Lle\HermesBundle\Entity;

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
 */
#[ORM\Entity(repositoryClass: EmailErrorRepository::class)]
#[ORM\Table(name: 'lle_hermes_email_error')]
class EmailError
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'integer')]
    private int $nbError = 0;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\Length(max: 255)]
    #[Assert\Email]
    #[Assert\NotBlank]
    private string $email;

    #[ORM\OneToMany(targetEntity: Error::class, mappedBy: 'emailError')]
    private Collection $errors;

    public function __construct()
    {
        $this->errors = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->email;
    }

    public function incrementNbError(): EmailError
    {
        $this->nbError++;

        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): EmailError
    {
        $this->id = $id;

        return $this;
    }

    public function getNbError(): int
    {
        return $this->nbError;
    }

    public function setNbError(int $nbError): EmailError
    {
        $this->nbError = $nbError;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): EmailError
    {
        $this->email = $email;

        return $this;
    }

    public function getErrors(): Collection
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

    public function removeError(Error $error): EmailError
    {
        if (true === $this->errors->contains($error)) {
            $this->errors->removeElement($error);
        }

        return $this;
    }
}
