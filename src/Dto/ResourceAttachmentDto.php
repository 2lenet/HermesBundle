<?php

namespace Lle\HermesBundle\Dto;

use Lle\HermesBundle\Contracts\AttachmentInterface;

class ResourceAttachmentDto implements AttachmentInterface
{
    protected string $path;

    protected string $name;

    protected string $contentType;

    public function __construct(string $path, string $name, string $contentType)
    {
        $this->path = $path;
        $this->name = $name;
        $this->contentType = $contentType;
    }

    /**
     * @inheritdoc
     */
    public function getData(): ?string
    {
        $data = file_get_contents($this->path);

        return $data ?: null;
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
