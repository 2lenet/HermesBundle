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
     * @var string
     * Contact's general name
     */
    protected $name;

    protected $data = [];


    /**
     * @var string
     * Contact's mail address
     */
    protected $address;

    public function __construct(string $name, string $address)
    {
        $this->name = $name;
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return ContactDto
     */
    public function setData(array $data): ContactDto
    {
        $this->data = $data;
        return $this;
    }
}
