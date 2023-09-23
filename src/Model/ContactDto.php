<?php

namespace Lle\HermesBundle\Model;

/**
 * Class Contact
 * @package Lle\HermesBundle\Entity
 * Represent a Hermes contact.
 */
class ContactDto
{
    /**
     * Contact's general name
     */
    protected string $name;
    protected array $data = [];
    /**
     * Contact's mail address
     */
    protected string $address;

    public function __construct(string $name, string $address)
    {
        $this->name = $name;
        $this->address = $address;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }
}
