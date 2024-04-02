<?php

namespace Lle\HermesBundle\Contracts;

interface AttachmentInterface
{
    /**
     * Get attachment as text
     */
    public function getData(): ?string;

    /**
     * Get the attachment's filename
     */
    public function getName(): string;

    /**
     * Get the attachement's type
     * (must be MIME type, e.g. application/pdf)
     * @see https://developer.mozilla.org/fr/docs/Web/HTTP/Basics_of_HTTP/MIME_types/Common_types
     */
    public function getContentType(): string;
}
