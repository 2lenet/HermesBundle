<?php


namespace Lle\HermesBundle\Model;


interface AttachmentInterface
{
    /**
     * Get attachment as text
     * @return string
     */
    public function getData(): string;

    /**
     * Get the attachment's filename
     * @return string
     */
    public function getName(): string;

    /**
     * Get the attachement's type
     * (must be MIME type, e.g. application/pdf)
     * @see https://developer.mozilla.org/fr/docs/Web/HTTP/Basics_of_HTTP/MIME_types/Common_types
     * @return string
     */
    public function getContentType(): string;
}
