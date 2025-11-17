<?php

namespace Lle\HermesBundle\Contracts;

use Lle\HermesBundle\Entity\Recipient;
use Lle\HermesBundle\Entity\Template;

interface LinkTransformerInterface
{
    /**
     * Allows you to add query parameters.
     * @return array an array with the new parameters. Hermès will add them automatically.
     */
    public function addQueryParameters(string $url, Recipient $recipient, ?Template $template): array;
}
