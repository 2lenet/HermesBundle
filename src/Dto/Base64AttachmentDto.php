<?php

namespace Lle\HermesBundle\Dto;

use Lle\HermesBundle\Contracts\AttachmentInterface;

class Base64AttachmentDto implements AttachmentInterface
{
    protected string $data;

    protected string $name;

    protected string $contentType;

    public function __construct(string $base64, string $name, string $contentType)
    {
        $this->data = $base64;
        $this->name = $name;
        $this->contentType = $contentType;
    }

    /**
     * @inheritdoc
     */
    public function getData(): ?string
    {
        return base64_decode($this->data);
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }
}
