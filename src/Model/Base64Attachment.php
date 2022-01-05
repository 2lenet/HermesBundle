<?php


namespace Lle\HermesBundle\Model;


class Base64Attachment implements AttachmentInterface
{
    /**
     * @var string
     */
    protected $data;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $contentType;

    public function __construct(string $base64, string $name, string $contentType)
    {
        $this->data = $base64;
        $this->name = $name;
        $this->contentType = $contentType;
    }

    /**
     * @inheritdoc
     */
    public function getBase64Data(): string
    {
        return $this->data;
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
