<?php


namespace Lle\HermesBundle\Model;


class ResourceAttachment implements AttachmentInterface
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $contentType;

    public function __construct(string $path, string $name, string $contentType)
    {
        $this->path = $path;
        $this->name = $name;
        $this->contentType = $contentType;
    }

    /**
     * @inheritdoc
     */
    public function getBase64Data(): string
    {
        return base64_encode(file_get_contents($this->path));
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
