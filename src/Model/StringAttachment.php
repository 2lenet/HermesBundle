<?php

namespace Lle\HermesBundle\Model;

class StringAttachment implements AttachmentInterface
{
    /** @var string */
    protected $data;

    /** @var string */
    protected $name;

    /** @var string */
    protected $contentType;

    public function __construct(string $data, string $name, string $contentType)
    {
        $this->data = $data;
        $this->name = $name;
        $this->contentType = $contentType;
    }
    
    public function getData(): string
    {
        return $this->data;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }
}