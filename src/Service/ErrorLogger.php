<?php

namespace Lle\HermesBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\HermesBundle\Entity\Recipient;

class ErrorLogger
{
    public function __construct(
        protected EntityManagerInterface $em,
    ) {
    }

    public function logError(string $message, Recipient $recipient): void
    {
        $recipient->setErrorMessage($message);

        $this->em->flush();
    }
}
